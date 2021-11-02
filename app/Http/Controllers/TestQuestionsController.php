<?php

namespace tcCore\Http\Controllers;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use tcCore\DrawingQuestion;
use tcCore\Exceptions\QuestionException;
use tcCore\GroupQuestion;
use tcCore\Http\Helpers\QuestionHelper;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\CreateTestQuestionRequest;
use tcCore\Http\Requests\UpdateTestQuestionRequest;
use tcCore\Lib\Question\Factory;
use tcCore\Lib\Question\QuestionInterface;
use tcCore\Question;
use tcCore\QuestionAuthor;
use tcCore\TestQuestion;

class TestQuestionsController extends Controller {

    /**
     * Display a listing of the questions.
     *
     * @return Response
     */
    public function index(Request $request)
    {
        $testQuestions = TestQuestion::filtered($request->get('filter', []), $request->get('order', []));

        switch(strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                $testQuestions->with(['question', 'question.authors', 'question.attachments']);
                $testQuestions = $testQuestions->get();
                foreach($testQuestions as $testQuestion) {
                    if ($testQuestion->question instanceof GroupQuestion) {
                        $testQuestion->question->loadRelated(true);
                    } else {
                        $testQuestion->question->loadRelated();
                    }
                }

                return Response::make($testQuestions, 200);
                break;
            case 'list':
                $testQuestions->join('questions', 'questions.id', '=', 'test_questions.question_id');
                return Response::make($testQuestions->get(['test_questions.id', 'test_questions.question_id', 'questions.question', 'questions.type', 'test_questions.order'])->keyBy('id'), 200);
                break;
            case 'paginate':
            default:
                $testQuestions->with(['question', 'question.authors', 'question.attachments']);
                $testQuestions = $testQuestions->paginate(15);
                foreach($testQuestions as $testQuestion) {
                    if ($testQuestion->question instanceof GroupQuestion) {
                        $testQuestion->question->loadRelated();
                    }
                }
                return Response::make($testQuestions, 200);
                break;
        }
    }

    /**
     * Store a newly created question in storage.
     *
     * @param CreateTestQuestionRequest $request
     * @return Response
     */
    public function store(CreateTestQuestionRequest $request)
    {
        DB::beginTransaction();
        try{
            if ($request->get('question_id') === null) {
                $testQuestion = TestQuestion::store($request->all());
            } else {
                $testQuestion = new TestQuestion();

                $qHelper = new QuestionHelper();
                $questionData = [];
                if($request->get('type') == 'CompletionQuestion') {
                    $questionData = $qHelper->getQuestionStringAndAnswerDetailsForSavingCompletionQuestion($request->input('question'));
                }
                $totalData = array_merge($request->all(),$questionData);
                $question = Question::find($request->get('question_id'));
                if($question->is_subquestion){
                    $questionCopy = $question->duplicate([]);
                    $questionCopy->getQuestionInstance()->setAttribute('is_subquestion', 0);
                    $totalData = array_merge($totalData,['question_id'=>$questionCopy->getKey()]);
                    $questionCopy->getQuestionInstance()->save();
                }
                $testQuestion->fill($totalData);

//                if($request->get('type') == 'CompletionQuestion') {
//                    /**
//                     * we don't need to check if this works, as there's an exception thrown on failure
//                     */
//                    $qHelper->storeAnswersForCompletionQuestion($testQuestion, $questionData['answers']);
//                }
                if ($testQuestion->save()) {
                    if(Question::usesDeleteAndAddAnswersMethods($request->get('type'))) {
//                        // delete old answers
//                        $question->deleteAnswers($question);

                        // add new answers
                        $testQuestion->question->addAnswers($testQuestion,$totalData['answers']);
                    }
                    $testQuestion->addCloneAttachmentsIfAppropriate($totalData);
                    // don't return here as the DB::commit() needs to be done first.
                    //return Response::make($testQuestion, 200);
                } else {
                    throw new QuestionException('Failed to create test question');
                }
                $question = $testQuestion->question;
                if (!QuestionAuthor::addAuthorToQuestion($question)) {
                    throw new QuestionException('Failed to attach author to question');
                }
            }

        }
        catch(\Exception $e){
            DB::rollback();
            logger($e->getMessage());
            return Response::make($e->getMessage(),500);
        }
        DB::commit();
        return Response::make($testQuestion, 200);

    }

    /**
     * Display the specified question.
     *
     * @param  TestQuestion  $question
     * @return Response
     */
    public function show(TestQuestion $testQuestion)
    {
        if($testQuestion->question instanceof QuestionInterface) {
            $testQuestion->question->loadRelated();
            with($testQuestion->question->getQuestionInstance())->load(['attachments', 'attainments', 'authors', 'tags', 'pValue' => function($query) {
                $query->select('question_id', 'education_level_id', 'education_level_year', DB::raw('(SUM(score) / SUM(max_score)) as p_value'), DB::raw('count(1) as p_value_count'))->groupBy('education_level_id')->groupBy('education_level_year');
            }, 'pValue.educationLevel']);
        }

        return Response::make($testQuestion, 200);
    }

    /**
     * Update the specified question order in storage.
     *
     * @param  Question $question
     * @param UpdateTestQuestionRequest $request
     * @return Response
     */
    // UpdateTestQuestionRequest
    public function updateOrder(TestQuestion $testQuestion,  UpdateTestQuestionRequest $request)
    {
        // Fill and check if question is modified
        $question = $testQuestion->question;

        DB::beginTransaction();
        try {
            $question->fill($request->all());
            $questionInstance = $question->getQuestionInstance();

            $testQuestion->fill($request->all());
            // If question is modified and cannot be saved without effecting other things, duplicate and re-attach
            if (    $question->isDirty()
                    || $questionInstance->isDirty()
                    || $questionInstance->isDirtyAttainments()
                    || $questionInstance->isDirtyTags()
                    || ($question instanceof DrawingQuestion && $question->isDirtyFile()))
            {
                if ($question->isUsed($testQuestion)) {
                    $message = 'GM says at june 15th 2021: TestQuestionsController line 171. We will never get here. After three months, check bugsnack and than remove this code';
                    Bugsnag::notifyException(new \Exception($message));
                    $question = $question->duplicate($request->all());
                    if ($question === false) {
                        throw new QuestionException('Failed to duplicate question');
                    }

                    $testQuestion->setAttribute('question_id', $question->getKey());
                } elseif (!$questionInstance->save() || !$question->save()) {
                    throw new QuestionException('Failed to save question');
                }
            }

            if ($testQuestion->save()) {
            } else {
                throw new QuestionException('Failed to update test question');
            }
        }
        catch(QuestionException $e){
            DB::rollback();
            $e->sendExceptionMail();
            return Response::make($e->getMessage(),422);
        }
        DB::commit();
        return Response::make($testQuestion, 200);
    }


    /**
     * Update the specified question in storage.
     *
     * @param  Question $question
     * @param UpdateTestQuestionRequest $request
     * @return Response
     */

    public function update(TestQuestion $testQuestion,  UpdateTestQuestionRequest $request)
    {
        return $this->updateGeneric($testQuestion, $request);
    }



    public function updateFromWithin(TestQuestion $testQuestion,  Request $request)
    {
        return $this->updateGeneric($testQuestion, $request);
    }

    protected function updateGeneric(TestQuestion $testQuestion, $request)
    {
        $question = $testQuestion->question;
        DB::beginTransaction();
        try {
            $testQuestion->fill($request->all());
            $question->updateWithRequest($request,$testQuestion);
            $testQuestion->setAttribute('question_id', $question->getKeyAfterPossibleDuplicate());

            if ($testQuestion->save()) {
                $testQuestion->refresh();
                $question = $testQuestion->question;
                $question->handleAnswersAfterOwnerModelUpdate($testQuestion,$request);
            } else {
                throw new QuestionException('Failed to update test question');
            }
        }
        catch(QuestionException $e){
            DB::rollback();
            $e->sendExceptionMail();
            return Response::make($e->getMessage(),422);
        }
        DB::commit();
        return Response::make($testQuestion, 200);
    }

    /**
     * Remove the specified question from storage.
     *
     * @param  Question  $question
     * @return Response
     */
    public function destroy(TestQuestion $testQuestion)
    {
        $question = $testQuestion->question;

        if ($testQuestion->delete()) {
            if (!$question->isUsed($testQuestion)) {
                $testQuestion->question->delete();
            }
            return Response::make($testQuestion, 200);
        } else {
            return Response::make('Failed to delete test question', 500);
        }
    }

}

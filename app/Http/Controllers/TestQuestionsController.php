<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use tcCore\DrawingQuestion;
use tcCore\GroupQuestion;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\CreateTestQuestionRequest;
use tcCore\Http\Requests\UpdateTestQuestionRequest;
use tcCore\Lib\Question\Factory;
use tcCore\Lib\Question\QuestionInterface;
use tcCore\Question;
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
        if ($request->get('question_id') === null) {
            $question = Factory::makeQuestion($request->get('type'));
            if (!$question) {
                return Response::make('Failed to create question with factory', 500);
            }

            $testQuestion = new TestQuestion();
            $testQuestion->fill($request->all());
            $test = $testQuestion->test;

            $question->fill($request->all());

            $questionInstance = $question->getQuestionInstance();
            if ($questionInstance->getAttribute('subject_id') === null) {
                $questionInstance->setAttribute('subject_id', $test->subject->getKey());
            }

            if ($questionInstance->getAttribute('education_level_id') === null) {
                $questionInstance->setAttribute('education_level_id', $test->educationLevel->getKey());
            }

            if ($questionInstance->getAttribute('education_level_year') === null) {
                $questionInstance->setAttribute('education_level_year', $test->getAttribute('education_level_year'));
            }

            if ($question->save()) {
                $testQuestion->setAttribute('question_id', $question->getKey());

                if ($testQuestion->save()) {
                    return Response::make($testQuestion, 200);
                } else {
                    return Response::make('Failed to create test question', 500);
                }
            } else {
                return Response::make('Failed to create question', 500);
            }
        } else {
            $testQuestion = new TestQuestion();
            $testQuestion->fill($request->all());
            if ($testQuestion->save()) {
                return Response::make($testQuestion, 200);
            } else {
                return Response::make('Failed to create test question', 500);
            }
        }
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
     * Update the specified question in storage.
     *
     * @param  Question $question
     * @param UpdateTestQuestionRequest $request
     * @return Response
     */
    public function update(TestQuestion $testQuestion, UpdateTestQuestionRequest $request)
    {
        // Fill and check if question is modified
        $question = $testQuestion->question;

        $question->fill($request->all());
        $questionInstance = $question->getQuestionInstance();

        $testQuestion->fill($request->all());

        // If question is modified and cannot be saved without effecting other things, duplicate and re-attach
        if ($question->isDirty() || $questionInstance->isDirty() || $questionInstance->isDirtyAttainments() || $questionInstance->isDirtyTags() || ($question instanceof DrawingQuestion && $question->isDirtyFile())) {
            if ($question->isUsed($testQuestion)) {
                $question = $question->duplicate($request->all());
                if ($question === false) {
                    return Response::make('Failed to duplicate question', 500);
                }

                $testQuestion->setAttribute('question_id', $question->getKey());
            } elseif(!$questionInstance->save() || !$question->save()) {
                return Response::make('Failed to save question', 500);
            }
        }

        Log::debug($testQuestion->toSql());
        DB::enableQueryLog();
        // Save the link
        if ($testQuestion->save()) {
            Log::debug(DB::getQueryLog());
            return Response::make($testQuestion, 200);
        } else {
            return Response::make('Failed to update test question', 500);
        }
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

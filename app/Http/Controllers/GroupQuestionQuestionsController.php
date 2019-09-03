<?php namespace tcCore\Http\Controllers;

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
use tcCore\Http\Requests\CreateGroupQuestionQuestionRequest;
use tcCore\Http\Requests\UpdateGroupQuestionQuestionRequest;
use tcCore\Lib\GroupQuestionQuestion\GroupQuestionQuestionManager;
use tcCore\Lib\Question\Factory;
use tcCore\Lib\Question\QuestionInterface;
use tcCore\Question;
use tcCore\GroupQuestionQuestion;

class GroupQuestionQuestionsController extends Controller
{

    /**
     * Display a listing of the questions.
     *
     * @return Response
     */
    public function index(GroupQuestionQuestionManager $groupQuestionQuestionManager, Request $request)
    {
        $question = $this->getAndValidateQuestionFromGroupQuestionQuestionManager($groupQuestionQuestionManager);

        $groupQuestionQuestions = $question->groupQuestionQuestions()->filtered($request->get('filter', []), $request->get('order', []));

        switch (strtolower($request->get('mode', 'paginate'))) {
            case 'all':
                $groupQuestionQuestions->with('question');
                $groupQuestionQuestions = $groupQuestionQuestions->get();
                foreach ($groupQuestionQuestions as $groupQuestionQuestion) {
                    if ($groupQuestionQuestion->question instanceof GroupQuestion) {
                        $groupQuestionQuestion->question->loadRelated(true);
                    } else {
                        $groupQuestionQuestion->question->loadRelated();
                    }
                }
                return Response::make($groupQuestionQuestions, 200);
                break;
            case 'list':
                $groupQuestionQuestions->join('questions', 'questions.id', '=', 'group_question_questions.question_id');
                return Response::make($groupQuestionQuestions->get(['group_question_questions.id', 'group_question_questions.question_id', 'questions.question', 'questions.type', 'group_question_questions.order'])->keyBy('id'), 200);
                break;
            case 'paginate':
            default:
                $groupQuestionQuestions->with('question');
                $groupQuestionQuestions = $groupQuestionQuestions->paginate(15);
                foreach ($groupQuestionQuestions as $groupQuestionQuestion) {
                    if ($groupQuestionQuestion->question instanceof GroupQuestion) {
                        $groupQuestionQuestion->question->loadRelated();
                    }
                }
                return Response::make($groupQuestionQuestions, 200);
                break;
        }
    }

    /**
     * Store a newly created question in storage.
     *
     * @param CreateGroupQuestionQuestionRequest $request
     * @return Response
     */
    public function store(GroupQuestionQuestionManager $groupQuestionQuestionManager, CreateGroupQuestionQuestionRequest $request)
    {

        $this->getAndValidateQuestionFromGroupQuestionQuestionManager($groupQuestionQuestionManager);

        if ($groupQuestionQuestionManager->isUsed()) {
            $groupQuestionQuestionManager->prepareForChange();
        }

        $groupQuestion = $this->getAndValidateQuestionFromGroupQuestionQuestionManager($groupQuestionQuestionManager);
        DB::beginTransaction();
        try {
            if ($request->get('question_id') === null) {

                $question = Factory::makeQuestion($request->get('type'));
                if (!$question) {
                    throw new QuestionException('Failed to create question with factory', 500);
                }

                $groupQuestionQuestion = new GroupQuestionQuestion();
                $groupQuestionQuestion->fill($request->all());
                $groupQuestionQuestion->setAttribute('group_question_id', $groupQuestion->getKey());
                $groupQuestion = $groupQuestionQuestion->groupQuestion;

                $qHelper = new QuestionHelper();
                $questionData = [];
                if ($request->get('type') == 'CompletionQuestion') {
                    $questionData = $qHelper->getQuestionStringAndAnswerDetailsForSavingCompletionQuestion($request->input('question'));
                }
                $totalData = array_merge($request->all(),$questionData);
                $question->fill(array_merge($totalData));

                $questionInstance = $question->getQuestionInstance();
                if ($questionInstance->getAttribute('subject_id') === null) {
                    $questionInstance->setAttribute('subject_id', $groupQuestion->subject->getKey());
                }

                if ($questionInstance->getAttribute('education_level_id') === null) {
                    $questionInstance->setAttribute('education_level_id', $groupQuestion->educationLevel->getKey());
                }

                if ($questionInstance->getAttribute('education_level_year') === null) {
                    $questionInstance->setAttribute('education_level_year', $groupQuestion->getAttribute('education_level_year'));
                }

                $questionInstance->setAttribute('is_subquestion', 1);

                if ($question->save()) {

                    $groupQuestionQuestion->setAttribute('question_id', $question->getKey());

//                    if($request->get('type') == 'CompletionQuestion') {
//                        /**
//                         * we don't need to check if this works, as there's an exception thrown on failure
//                         */
//                        $qHelper->storeAnswersForCompletionQuestion($groupQuestionQuestion, $questionData['answers']);
//                    }

                    if ($groupQuestionQuestion->save()) {
                        if($request->get('type') == 'CompletionQuestion' || $request->get('type') == 'MatchingQuestion') {
//                        // delete old answers
//                        $question->deleteAnswers($question);

                            // add new answers
                            $groupQuestionQuestion->question->addAnswers($groupQuestionQuestion, $totalData['answers']);
                        }
                        $groupQuestionQuestion->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());
//                        return Response::make($groupQuestionQuestion, 200);
                    } else {
                        throw new QuestionException('Failed to create group question question', 500);
                    }
                } else {
                    throw new QuestionException('Failed to create question', 500);
                }

            } else {
                $groupQuestionQuestion = new GroupQuestionQuestion();
                $qHelper = new QuestionHelper();
                $questionData = [];
                if ($request->get('type') == 'CompletionQuestion') {
                    $questionData = $qHelper->getQuestionStringAndAnswerDetailsForSavingCompletionQuestion($request->input('question'));
                }
                $groupQuestionQuestion->fill(array_merge($request->all(), $questionData));

//                if($request->get('type') == 'CompletionQuestion') {
//                    /**
//                     * we don't need to check if this works, as there's an exception thrown on failure
//                     */
//                    $qHelper->storeAnswersForCompletionQuestion($groupQuestionQuestion, $questionData['answers']);
//                }

                $groupQuestionQuestion->setAttribute('group_question_id', $groupQuestion->getKey());
                if ($groupQuestionQuestion->save()) {
                    if($request->get('type') == 'CompletionQuestion' || $request->get('type') == 'MatchingQuestion') {
//                        // delete old answers
//                        $question->deleteAnswers($question);

                        // add new answers
                        $groupQuestionQuestion->question->addAnswers($groupQuestionQuestion, $questionData['answers']);
                    }

                    $groupQuestionQuestion->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());
//                    return Response::make($groupQuestionQuestion, 200);
                } else {
                    throw new QuestionException('Failed to create group question question', 500);
                }
            }
        } catch (\Exception $e) {
            DB::rollback();
            return Response::make($e->getMessage(), 500);
        }
        DB::commit();
        return Response::make($groupQuestionQuestion, 200);
    }

    /**
     * Display the specified question.
     *
     * @param  GroupQuestionQuestion $question
     * @return Response
     */
    public function show(GroupQuestionQuestionManager $groupQuestionQuestionManager, GroupQuestionQuestion $group_question_question_id)
    {
        $groupQuestionQuestion = $group_question_question_id;
        if (!$groupQuestionQuestionManager->isChild($groupQuestionQuestion)) {
            return Response::make('Group question question not found', 404);
        }

        if ($groupQuestionQuestion->question instanceof QuestionInterface) {
            $groupQuestionQuestion->question->loadRelated();
            with($groupQuestionQuestion->question->getQuestionInstance())->load(['attachments', 'attainments', 'authors', 'tags', 'pValue' => function ($query) {
                $query->select('question_id', 'education_level_id', 'education_level_year', DB::raw('(SUM(score) / SUM(max_score)) as p_value'), DB::raw('count(1) as p_value_count'))->groupBy('education_level_id')->groupBy('education_level_year');
            }, 'pValue.educationLevel']);
        }

        return Response::make($groupQuestionQuestion, 200);
    }

    /**
     * Update the specified question order in storage.
     *
     * @param  Question $question
     * @param UpdateGroupQuestionQuestionRequest $request
     * @return Response
     */
    public function updateOrder(GroupQuestionQuestionManager $groupQuestionQuestionManager, GroupQuestionQuestion $group_question_question_id, UpdateGroupQuestionQuestionRequest $request)
    {
        $groupQuestionQuestion = $group_question_question_id;
        if (!$groupQuestionQuestionManager->isChild($groupQuestionQuestion)) {
            return Response::make('Group question question not found', 404);
        }

        // Fill and check if question is modified
        $question = $groupQuestionQuestion->question;
        DB::beginTransaction();
        try {
            $qHelper = new QuestionHelper();

            $question->fill($request->all());
            $questionInstance = $question->getQuestionInstance();

            $groupQuestionQuestionOriginal = $groupQuestionQuestion;
            $groupQuestionQuestion->fill($request->all());

            // $groupQuestionQuestionManager->isUsed();


            if (
                ($groupQuestionQuestionManager->isUsed() || $question->isUsed($groupQuestionQuestion)) &&
                ($question->isDirty() || $questionInstance->isDirty() || $questionInstance->isDirtyAttainments() || $questionInstance->isDirtyTags() || ($question instanceof DrawingQuestion && $question->isDirtyFile()))) {
                // return Response::make(var_dump($groupQuestionQuestionManager), 500);
                $testQuestion = $groupQuestionQuestionManager->prepareForChange($groupQuestionQuestion);
                $groupQuestionQuestion = $groupQuestionQuestion->duplicate(
                    $groupQuestionQuestionManager->getQuestionLink()->question,
                    [
                        'group_question_id' => $groupQuestionQuestionManager->getQuestionLink()->getAttribute('group_question')
                    ]
                );

                $question = $groupQuestionQuestion->question;
                $question->fill($request->all());
                $questionInstance = $question->getQuestionInstance();

                $groupQuestionQuestion->setAttribute('group_question_id', $testQuestion->getAttribute('question_id'));

                // return Response::make(json_encode($testQuestion->getAttribute('question_id')), 500);
            }

            // If question is modified and cannot be saved without effecting other things, duplicate and re-attach
            if ($question->isDirty() || $questionInstance->isDirty() || $questionInstance->isDirtyAttainments() || $questionInstance->isDirtyTags() || ($question instanceof DrawingQuestion && $question->isDirtyFile())) {
                if ($question->isUsed($groupQuestionQuestion) || $groupQuestionQuestionManager->isUsed()) {
                    $question = $question->duplicate($request->all());
                    if ($question === false) {
                        throw new QuestionException('Failed to duplicate question', 422);
                    }

                    $groupQuestionQuestion->setAttribute('question_id', $question->getKey());
                } elseif (!$questionInstance->save() || !$question->save()) {
                    throw new QuestionException('Failed to save question', 422);
                }
            }
            // return Response::make(var_dump( $groupQuestionQuestionManager->getQuestionLink()->getAttribute('question_id') ), 500);

            // $groupQuestionQuestion->setAttribute('group_question_id', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());

            // Save the link
            if ($groupQuestionQuestion->save()) {
                $groupQuestionQuestion->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());
//                return Response::make($groupQuestionQuestion, 200);
            } else {
                throw new QuestionException('Failed to update group question question', 422);
            }
        } catch (QuestionException $e) {
            DB::rollback();
            $e->sendExceptionMail();
            return Response::make($e->getMessage(), 422);
        }
        DB::commit();
        return Response::make($groupQuestionQuestion, 200);
    }

    /**
     * Update the specified question in storage.
     *
     * @param  Question $question
     * @param UpdateGroupQuestionQuestionRequest $request
     * @return Response
     */
    public function update(GroupQuestionQuestionManager $groupQuestionQuestionManager, GroupQuestionQuestion $group_question_question_id, UpdateGroupQuestionQuestionRequest $request)
    {

        $groupQuestionQuestion = $group_question_question_id;
        if (!$groupQuestionQuestionManager->isChild($groupQuestionQuestion)) {
            return Response::make('Group question question not found', 404);
        }

        // Fill and check if question is modified
        $question = $groupQuestionQuestion->question;
        DB::beginTransaction();
        try {
            $qHelper = new QuestionHelper();
            $questionData = [];
            if ($question->getQuestionInstance()->type == 'CompletionQuestion') {
                $questionData = $qHelper->getQuestionStringAndAnswerDetailsForSavingCompletionQuestion($request->input('question'));
            }

            $totalData = array_merge($request->all(),$questionData);

            $question->fill($totalData);

            $questionInstance = $question->getQuestionInstance();

            $groupQuestionQuestionOriginal = $groupQuestionQuestion;
            $groupQuestionQuestion->fill($request->all());


            if (
                ($groupQuestionQuestionManager->isUsed() || $question->isUsed($groupQuestionQuestion)) &&
                ($question->isDirty() || $questionInstance->isDirty() || $questionInstance->isDirtyAttainments() || $questionInstance->isDirtyTags() || ($question instanceof DrawingQuestion && $question->isDirtyFile()))) {
                // return Response::make(var_dump($groupQuestionQuestionManager), 500);
                $testQuestion = $groupQuestionQuestionManager->prepareForChange($groupQuestionQuestion);
                $groupQuestionQuestion = $groupQuestionQuestion->duplicate(
                    $groupQuestionQuestionManager->getQuestionLink()->question,
                    [
                        'group_question_id' => $groupQuestionQuestionManager->getQuestionLink()->getAttribute('group_question')
                    ]
                );

                $question = $groupQuestionQuestion->question;
                $question->fill($request->all());
                $questionInstance = $question->getQuestionInstance();

                $groupQuestionQuestion->setAttribute('group_question_id', $testQuestion->getAttribute('question_id'));

                // return Response::make(json_encode($testQuestion->getAttribute('question_id')), 500);
            }

            // If question is modified and cannot be saved without effecting other things, duplicate and re-attach
            if ($question->isDirty() || $questionInstance->isDirty() || $questionInstance->isDirtyAttainments() || $questionInstance->isDirtyTags() || ($question instanceof DrawingQuestion && $question->isDirtyFile())) {
                if ($question->isUsed($groupQuestionQuestion) || $groupQuestionQuestionManager->isUsed()) {
                    $question = $question->duplicate($request->all());
                    if ($question === false) {
                        throw new QuestionException('Failed to duplicate question', 422);
                    }

                    $groupQuestionQuestion->setAttribute('question_id', $question->getKey());
                } elseif (!$questionInstance->save() || !$question->save()) {
                    throw new QuestionException('Failed to save question', 422);
                }
            }
            // return Response::make(var_dump( $groupQuestionQuestionManager->getQuestionLink()->getAttribute('question_id') ), 500);

            // $groupQuestionQuestion->setAttribute('group_question_id', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());

            // Save the link
            if ($groupQuestionQuestion->save()) {
                if ($questionInstance->type == 'CompletionQuestion') {
                    // delete old answers
                    $question->deleteAnswers($question);

                    // add new answers
                    $question->addAnswers($groupQuestionQuestion, $totalData['answers']);
                }
                $groupQuestionQuestion->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());
//                return Response::make($groupQuestionQuestion, 200);
            } else {
                throw new QuestionException('Failed to update group question question', 422);
            }
        } catch (QuestionException $e) {
            DB::rollback();
            $e->sendExceptionMail();
            return Response::make($e->getMessage(), 422);
        }
        DB::commit();
        return Response::make($groupQuestionQuestion, 200);
    }

    /**
     * Remove the specified question from storage.
     *
     * @param  Question $question
     * @return Response
     */
    public function destroy(GroupQuestionQuestionManager $groupQuestionQuestionManager, GroupQuestionQuestion $group_question_question_id)
    {
        $groupQuestionQuestion = $group_question_question_id;
        if (!$groupQuestionQuestionManager->isChild($groupQuestionQuestion)) {
            return Response::make('Group question question not found', 404);
        }

        if ($groupQuestionQuestionManager->isUsed()) {
            $groupQuestionQuestionManager->prepareForChange($groupQuestionQuestion);
            $groupQuestionQuestion->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());
            return Response::make($groupQuestionQuestion, 200);
        } else {
            $question = $groupQuestionQuestion->question;

            if (!$question->isUsed($groupQuestionQuestion)) {
                $groupQuestionQuestion->question->delete();
            }

            if ($groupQuestionQuestion->delete()) {
                $groupQuestionQuestion->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());
                return Response::make($groupQuestionQuestion, 200);
            } else {
                return Response::make('Failed to delete group question question', 500);
            }
        }
    }

    protected function getAndValidateQuestionFromGroupQuestionQuestionManager(GroupQuestionQuestionManager $groupQuestionQuestionManager)
    {
        $question = $groupQuestionQuestionManager->getQuestionLink()->question;
        if ($question === null) {
            return Response::make('Question not not exist.', 404);
        }

        if (!$question instanceof GroupQuestion) {
            return Response::make('Question does not allow group question questions.', 404);
        }

        return $question;
    }
}

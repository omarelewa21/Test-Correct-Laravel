<?php namespace tcCore\Http\Controllers\GroupQuestionQuestions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\CompletionQuestionAnswer;
use tcCore\Http\Requests\CreateCompletionQuestionAnswerRequest;
use tcCore\Http\Requests\UpdateCompletionQuestionAnswerRequest;
use tcCore\CompletionQuestionAnswerLink;
use tcCore\Lib\GroupQuestionQuestion\GroupQuestionQuestionManager;
use tcCore\QuestionAuthor;
use tcCore\GroupQuestionQuestion;

class CompletionQuestionAnswersController extends Controller {

    /**
     * Display a listing of the completion question answers.
     *
     * @return Response
     */
    public function index(GroupQuestionQuestionManager $groupQuestionQuestionManager, Request $request)
    {
        $groupQuestionQuestion = $groupQuestionQuestionManager->getQuestionLink();
        $question = $groupQuestionQuestion->question;
        if (($response = $this->validateQuestion($question)) !== true) {
            return $response;
        } else {
            $completionQuestionAnswers = $question->completionQuestionAnswers()->filtered($request->get('filter', []), $request->get('order', []));

            switch(strtolower($request->get('mode', 'paginate'))) {
                case 'all':
                    return Response::make($completionQuestionAnswers->get(), 200);
                    break;
                case 'list':
                    return Response::make($completionQuestionAnswers->get(['title'])->keyBy('id'), 200);
                    break;
                case 'paginate':
                default:
                    return Response::make($completionQuestionAnswers->paginate(15), 200);
                    break;
            }
        }
    }

    /**
     * Store a newly created completion question answer in storage.
     *
     * @param CreateCompletionQuestionAnswerRequest $request
     * @return Response
     */
    public function store(GroupQuestionQuestionManager $groupQuestionQuestionManager, CreateCompletionQuestionAnswerRequest $request)
    {
        $groupQuestionQuestion = $groupQuestionQuestionManager->getQuestionLink();
        $question = $groupQuestionQuestion->question;
        if (($response = $this->validateQuestion($question)) !== true) {
            return $response;
        } else {
            if ($question->isUsed($groupQuestionQuestion)) {
                $question = $question->duplicate([]);
                if ($question === false) {
                    return Response::make('Failed to duplicate question', 500);
                }

                $groupQuestionQuestion->setAttribute('question_id', $question->getKey());

                if (!$groupQuestionQuestion->save()) {
                    return Response::make('Failed to update group question question', 500);
                }
            }

            if (!QuestionAuthor::addAuthorToQuestion($question)) {
                return Response::make('Failed to attach author to question', 500);
            }

            $completionQuestionAnswer = new CompletionQuestionAnswer();

            $completionQuestionAnswer->fill($request->only($completionQuestionAnswer->getFillable()));
            if (!$completionQuestionAnswer->save()) {
                return Response::make('Failed to create completion question answer', 500);
            }

            $completionQuestionAnswerLink = new CompletionQuestionAnswerLink();
            $completionQuestionAnswerLink->fill($request->only($completionQuestionAnswerLink->getFillable()));
            $completionQuestionAnswerLink->setAttribute('completion_question_id', $question->getKey());
            $completionQuestionAnswerLink->setAttribute('completion_question_answer_id', $completionQuestionAnswer->getKey());

            if($completionQuestionAnswerLink->save()) {
                $completionQuestionAnswer->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());
                return Response::make($completionQuestionAnswer, 200);
            } else {
                return Response::make('Failed to create completion question answer link', 500);
            }
        }
    }

    /**
     * Display the specified completion question answer.
     *
     * @param  CompletionQuestionAnswer  $completionQuestionAnswer
     * @return Response
     */
    public function show(GroupQuestionQuestionManager $groupQuestionQuestionManager, CompletionQuestionAnswer $completionQuestionAnswer)
    {
        $groupQuestionQuestion = $groupQuestionQuestionManager->getQuestionLink();
        $question = $groupQuestionQuestion->question;
        if (($response = $this->validateQuestion($groupQuestionQuestion)) !== true) {
            return $response;
        } else {
            if (!$this->checkLinkExists($question, $completionQuestionAnswer)) {
                return Response::make('completion question answer not found', 404);
            }

            return Response::make($completionQuestionAnswer, 200);
        }
    }

    /**
     * Update the specified completion question answer in storage.
     *
     * @param  CompletionQuestionAnswer $completionQuestionAnswer
     * @param UpdateCompletionQuestionAnswerRequest $request
     * @return Response
     */
    public function update(GroupQuestionQuestionManager $groupQuestionQuestionManager, CompletionQuestionAnswer $completionQuestionAnswer, UpdateCompletionQuestionAnswerRequest $request)
    {
        $groupQuestionQuestion = $groupQuestionQuestionManager->getQuestionLink();
        $question = $groupQuestionQuestion->question;
        if (($response = $this->validateQuestion($question)) !== true) {
            return $response;
        } else {
            $completionQuestionAnswerLink = $question->completionQuestionAnswers()->withTrashed()->where('completion_question_answer_id', $completionQuestionAnswer->getKey())->first();
            if ($completionQuestionAnswerLink === null) {
                return Response::make('completion question answer not found', 404);
            }

            $completionQuestionAnswer->fill($request->all());
            $completionQuestionAnswerLink->fill($request->all());

            if ($completionQuestionAnswer->isDirty() || $completionQuestionAnswerLink->isDirty()) {
                if (($questionDuplicated = $question->isUsed($groupQuestionQuestion))) {
                    $question = $question->duplicate([], $completionQuestionAnswer);
                    if ($question === false) {
                        return Response::make('Failed to duplicate question', 500);
                    }

                    if ($completionQuestionAnswer->isDirty()) {
                        $completionQuestionAnswer = $completionQuestionAnswer->duplicate($question, $request->all());
                        if ($completionQuestionAnswer === false) {
                            return Response::make('Failed to duplicate and update completion question answer', 500);
                        }
                        $completionQuestionAnswerLink->setAttribute('completion_question_answer_id', $completionQuestionAnswer->getKey());
                    }

                    if (!QuestionAuthor::addAuthorToQuestion($question)) {
                        return Response::make('Failed to attach author to question', 500);
                    }

                    $groupQuestionQuestion->setAttribute('question_id', $question->getKey());
                    if ($questionDuplicated && !$groupQuestionQuestion->save()) {
                        return Response::make('Failed to update group question question', 500);
                    } else {
                        $completionQuestionAnswer->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());
                        return Response::make($completionQuestionAnswer, 200);
                    }
                } else {
                    if ($completionQuestionAnswer->isDirty()) {
                        $completionQuestionAnswer = $completionQuestionAnswer->duplicate($question, $request->all());
                        if ($completionQuestionAnswer === false) {
                            return Response::make('Failed to duplicate and update completion question answer', 500);
                        }
                        $completionQuestionAnswerLink->setAttribute('completion_question_answer_id', $completionQuestionAnswer->getKey());
                    }

                    if (!QuestionAuthor::addAuthorToQuestion($question)) {
                        return Response::make('Failed to attach author to question', 500);
                    }

                    if ($question->completionQuestionAnswers()->save($completionQuestionAnswer) !== false) {
                        $completionQuestionAnswer->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());
                        return Response::make($completionQuestionAnswer, 200);
                    } else {
                        return Response::make('Failed to update completion question answer', 500);
                    }
                }
            } else {
                $completionQuestionAnswer->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());
                return Response::make($completionQuestionAnswer, 200);
            }
        }
    }

    /**
     * Remove the specified completion question answer from storage.
     *
     * @param  CompletionQuestionAnswer  $completionQuestionAnswer
     * @return Response
     */
    public function destroy(GroupQuestionQuestionManager $groupQuestionQuestionManager, CompletionQuestionAnswer $completionQuestionAnswer)
    {
        $groupQuestionQuestion = $groupQuestionQuestionManager->getQuestionLink();
        $question = $groupQuestionQuestion->question;
        if (($response = $this->validateQuestion($question)) !== true) {
            return $response;
        } else {
            if (!$this->checkLinkExists($question, $completionQuestionAnswer)) {
                return Response::make('Completion question answer not found', 404);
            }

            if ($question->isUsed($groupQuestionQuestion)) {
                $question = $question->duplicate([], $completionQuestionAnswer);
                if ($question === false) {
                    return Response::make('Failed to duplicate question', 500);
                }

                $groupQuestionQuestion->setAttribute('question_id', $question->getKey());
                if (!$groupQuestionQuestion->save()) {
                    return Response::make('Failed to update group question question', 500);
                }
            }

            if (!QuestionAuthor::addAuthorToQuestion($question)) {
                return Response::make('Failed to attach author to question', 500);
            }

            $completionQuestionAnswerLink = $question->completionQuestionAnswers()->withTrashed()->where('completion_question_answer_id', $completionQuestionAnswer->getKey())->first();
            if (!$completionQuestionAnswerLink->delete()) {
                return Response::make('Failed to delete completion question answer link', 500);
            }

            if ($completionQuestionAnswer->isUsed($completionQuestionAnswerLink)) {
                $completionQuestionAnswer->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());
                return Response::make($completionQuestionAnswer, 200);
            } else {
                if ($completionQuestionAnswer->delete() ) {
                    $completionQuestionAnswer->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());
                    return Response::make($completionQuestionAnswer, 200);
                } else {
                    return Response::make('Failed to delete completion question answer', 500);
                }
            }
        }
    }

    public function destroyAll(GroupQuestionQuestionManager $groupQuestionQuestionManager) {
        $groupQuestionQuestion = $groupQuestionQuestionManager->getQuestionLink();
        $question = $groupQuestionQuestion->question;
        if (($response = $this->validateQuestion($question)) !== true) {
            return $response;
        } else {
            if ($question->isUsed($groupQuestionQuestion)) {
                $question = $question->duplicate([], null);
                if ($question === false) {
                    return Response::make('Failed to duplicate question', 500);
                }
            }

            if (!QuestionAuthor::addAuthorToQuestion($question)) {
                return Response::make('Failed to attach author to question', 500);
            }

            $completionQuestionAnswerLinks = $question->completionQuestionAnswerLinks()->with('completionQuestionAnswer')->get();
            if ($completionQuestionAnswerLinks->isEmpty()) {
                return Response::make(['group_question_question_path'  => $groupQuestionQuestionManager->getGroupQuestionQuestionPath(), 'completion_question_answer_links' => []], 200);
            } elseif ($question->completionQuestionAnswerLinks()->delete()) {
                foreach($completionQuestionAnswerLinks as $completionQuestionAnswerLink) {
                    $completionQuestionAnswer = $completionQuestionAnswerLink->completionQuestionAnswer;

                    // if($completionQuestionAnswer->isUsed($completionQuestionAnswerLink) && !$completionQuestionAnswer->delete()) {
                    //     Response::make('Failed to delete completion question answer', 500);
                    // }
                }
                return Response::make(['group_question_question_path'  => $groupQuestionQuestionManager->getGroupQuestionQuestionPath(), 'completion_question_answer_links' => $completionQuestionAnswerLinks], 200);
            } else {
                return Response::make('Failed to delete completion question answers', 500);
            }
        }
    }

    /**
     * Perform pre-action checks
     * @param GroupQuestionQuestion $question
     * @return bool
     */
    protected function validateQuestion($question) {
        if (!method_exists($question, 'completionQuestionAnswers')) {
            return Response::make('Question does not allow completion question answers.', 404);
        }

        return true;
    }

    protected function checkLinkExists($question, $completionQuestionAnswer) {
        return ($question->completionQuestionAnswers()->withTrashed()->where('completion_question_answer_id', $completionQuestionAnswer->getKey())->count() > 0);
    }
}

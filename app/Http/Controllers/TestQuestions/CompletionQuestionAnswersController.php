<?php namespace tcCore\Http\Controllers\TestQuestions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\CompletionQuestionAnswer;
use tcCore\Http\Requests\CreateCompletionQuestionAnswerRequest;
use tcCore\Http\Requests\UpdateCompletionQuestionAnswerRequest;
use tcCore\CompletionQuestionAnswerLink;
use tcCore\QuestionAuthor;
use tcCore\TestQuestion;

class CompletionQuestionAnswersController extends Controller {

    /**
     * Display a listing of the completion question answers.
     *
     * @return Response
     */
    public function index(TestQuestion $testQuestion, Request $request)
    {
        $question = $testQuestion->question;
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
    public function store(TestQuestion $testQuestion, CreateCompletionQuestionAnswerRequest $request)
    {
        $question = $testQuestion->question;
        if (($response = $this->validateQuestion($question)) !== true) {
            return $response;
        } else {
            if ($question->isUsed($testQuestion)) {
                $question = $question->duplicate([]);
                if ($question === false) {
                    return Response::make('Failed to duplicate question', 422);
                }

                $testQuestion->setAttribute('question_id', $question->getKey());

                if (!$testQuestion->save()) {
                    return Response::make('Failed to update test question', 422);
                }
            }

            if (!QuestionAuthor::addAuthorToQuestion($question)) {
                return Response::make('Failed to attach author to question', 422);
            }

            $completionQuestionAnswer = new CompletionQuestionAnswer();

            $completionQuestionAnswer->fill($request->only($completionQuestionAnswer->getFillable()));
            if (!$completionQuestionAnswer->save()) {
                return Response::make('Failed to create completion question answer', 422);
            }

            $completionQuestionAnswerLink = new CompletionQuestionAnswerLink();
            $completionQuestionAnswerLink->fill($request->only($completionQuestionAnswerLink->getFillable()));
            $completionQuestionAnswerLink->setAttribute('completion_question_id', $question->getKey());
            $completionQuestionAnswerLink->setAttribute('completion_question_answer_id', $completionQuestionAnswer->getKey());

            if($completionQuestionAnswerLink->save()) {
                return Response::make($completionQuestionAnswer, 200);
            } else {
                return Response::make('Failed to create completion question answer link', 422);
            }
        }
    }

    /**
     * Display the specified completion question answer.
     *
     * @param  CompletionQuestionAnswer  $completionQuestionAnswer
     * @return Response
     */
    public function show(TestQuestion $testQuestion, CompletionQuestionAnswer $completionQuestionAnswer)
    {
        $question = $testQuestion->question;
        if (($response = $this->validateQuestion($testQuestion)) !== true) {
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
    public function update(TestQuestion $testQuestion, CompletionQuestionAnswer $completionQuestionAnswer, UpdateCompletionQuestionAnswerRequest $request)
    {
        $question = $testQuestion->question;
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
                if (($questionDuplicated = $question->isUsed($testQuestion))) {
                    $question = $question->duplicate([], $completionQuestionAnswer);
                    if ($question === false) {
                        return Response::make('Failed to duplicate question', 422);
                    }

                    if ($completionQuestionAnswer->isDirty()) {
                        $completionQuestionAnswer = $completionQuestionAnswer->duplicate($question, $request->all());
                        if ($completionQuestionAnswer === false) {
                            return Response::make('Failed to duplicate and update completion question answer', 422);
                        }
                        $completionQuestionAnswerLink->setAttribute('completion_question_answer_id', $completionQuestionAnswer->getKey());
                    }

                    if (!QuestionAuthor::addAuthorToQuestion($question)) {
                        return Response::make('Failed to attach author to question', 4422);
                    }

                    $testQuestion->setAttribute('question_id', $question->getKey());
                    if ($questionDuplicated && !$testQuestion->save()) {
                        return Response::make('Failed to update test question', 422);
                    } else {
                        return Response::make($completionQuestionAnswer, 200);
                    }
                } else {
                    if ($completionQuestionAnswer->isDirty()) {
                        $completionQuestionAnswer = $completionQuestionAnswer->duplicate($question, $request->all());
                        if ($completionQuestionAnswer === false) {
                            return Response::make('Failed to duplicate and update completion question answer', 422);
                        }
                        $completionQuestionAnswerLink->setAttribute('completion_question_answer_id', $completionQuestionAnswer->getKey());
                    }

                    if (!QuestionAuthor::addAuthorToQuestion($question)) {
                        return Response::make('Failed to attach author to question', 422);
                    }

                    if ($question->completionQuestionAnswers()->save($completionQuestionAnswer) !== false) {
                        return Response::make($completionQuestionAnswer, 200);
                    } else {
                        return Response::make('Failed to update completion question answer', 422);
                    }
                }
            } else {
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
    public function destroy(TestQuestion $testQuestion, CompletionQuestionAnswer $completionQuestionAnswer)
    {
        $question = $testQuestion->question;
        if (($response = $this->validateQuestion($question)) !== true) {
            return $response;
        } else {
            if (!$this->checkLinkExists($question, $completionQuestionAnswer)) {
                return Response::make('Completion question answer not found', 404);
            }

            if ($question->isUsed($testQuestion)) {
                $question = $question->duplicate([], $completionQuestionAnswer);
                if ($question === false) {
                    return Response::make('Failed to duplicate question', 422);
                }

                $testQuestion->setAttribute('question_id', $question->getKey());
                if (!$testQuestion->save()) {
                    return Response::make('Failed to update test question', 422);
                }
            }

            if (!QuestionAuthor::addAuthorToQuestion($question)) {
                return Response::make('Failed to attach author to question', 422);
            }

            $completionQuestionAnswerLink = $question->completionQuestionAnswers()->withTrashed()->where('completion_question_answer_id', $completionQuestionAnswer->getKey())->first();
            if (!$completionQuestionAnswerLink->delete()) {
                return Response::make('Failed to delete completion question answer link', 422);
            }

            if ($completionQuestionAnswer->isUsed($completionQuestionAnswerLink)) {
                return Response::make($completionQuestionAnswer, 200);
            } else {
                if ($completionQuestionAnswer->delete() ) {
                    return Response::make($completionQuestionAnswer, 200);
                } else {
                    return Response::make('Failed to delete completion question answer', 422);
                }
            }
        }
    }

    public function destroyAll(TestQuestion $testQuestion) {
        $question = $testQuestion->question;
        if (($response = $this->validateQuestion($question)) !== true) {
            return $response;
        } else {
            if ($question->isUsed($testQuestion)) {
                $question = $question->duplicate([], null);
                if ($question === false) {
                    return Response::make('Failed to duplicate question', 422);
                }
            }

            if (!QuestionAuthor::addAuthorToQuestion($question)) {
                return Response::make('Failed to attach author to question', 422);
            }

            $completionQuestionAnswerLinks = $question->completionQuestionAnswerLinks()->with('completionQuestionAnswer')->get();
            if ($completionQuestionAnswerLinks->isEmpty()) {
                return Response::make([], 200);
            } elseif ($question->completionQuestionAnswerLinks()->delete()) {
                // foreach($completionQuestionAnswerLinks as $completionQuestionAnswerLink) {
                //     $completionQuestionAnswer = $completionQuestionAnswerLink->completionQuestionAnswer;

                //     if($completionQuestionAnswer->isUsed($completionQuestionAnswerLink) && !$completionQuestionAnswer->delete()) {
                //         Response::make('Failed to delete completion question answer', 422);
                //     }
                // }
                return Response::make($completionQuestionAnswerLinks, 200);
            } else {
                return Response::make('Failed to delete completion question answers', 422);
            }
        }
    }

    /**
     * Perform pre-action checks
     * @param TestQuestion $question
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

<?php namespace tcCore\Http\Controllers\TestQuestions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\Attachment;
use tcCore\Http\Requests\CreateAttachmentRequest;
use tcCore\Http\Requests\UpdateAttachmentRequest;
use tcCore\QuestionAttachment;
use tcCore\QuestionAuthor;
use tcCore\TestQuestion;

class AttachmentsController extends Controller {

    /**
     * Display a listing of the attachments.
     *
     * @return Response
     */
    public function index(TestQuestion $testQuestion, Request $request)
    {
        $question = $testQuestion->question;
        if (($response = $this->validateQuestion($question)) !== true) {
            return $response;
        } else {
            $attachments = $question->attachments()->filtered($request->get('filter', []), $request->get('order', []));

            switch(strtolower($request->get('mode', 'paginate'))) {
                case 'all':
                    return Response::make($attachments->get(), 200);
                    break;
                case 'list':
                    return Response::make($attachments->get(['title'])->keyBy('id'), 200);
                    break;
                case 'paginate':
                default:
                    return Response::make($attachments->paginate(15), 200);
                    break;
            }
        }
    }

    /**
     * Store a newly created attachment in storage.
     *
     * @param CreateAttachmentRequest $request
     * @return Response
     */
    public function store(TestQuestion $testQuestion, CreateAttachmentRequest $request)
    {
        $question = $testQuestion->question;
        if (($response = $this->validateQuestion($question)) !== true) {
            return $response;
        } else {
            $attachment = new Attachment();

            $attachment->fill($request->all());

            if ($question->isUsed($testQuestion)) {
                $question = $question->duplicate([]);
                if ($question === false) {
                    return Response::make('Failed to duplicate question', 500);
                }

                $testQuestion->setAttribute('question_id', $question->getKey());

                if (!$testQuestion->save()) {
                    return Response::make('Failed to update test question', 500);
                }
            }

            if (!QuestionAuthor::addAuthorToQuestion($question)) {
                return Response::make('Failed to attach author to question', 500);
            }

            if ($attachment->save() === false) {
                return Response::make('Failed to create attachment', 500);
            }

            $questionAttachment = new QuestionAttachment();
            $questionAttachment->setAttribute('question_id', $question->getKey());
            $questionAttachment->setAttribute('attachment_id', $attachment->getKey());
            $questionAttachment->setAttribute('options', $attachment->getAttribute('json'));

            if($questionAttachment->save()) {
                $attachment->setAttribute('group_question_question_path', '');
                return Response::make($attachment, 200);
            } else {
                return Response::make('Failed to create question attachment', 500);
            }
        }
    }

    /**
     * Display the specified attachment.
     *
     * @param  Attachment  $attachment
     * @return Response
     */
    public function show(TestQuestion $testQuestion, Attachment $attachment)
    {
        $question = $testQuestion->question;
        if (($response = $this->validateQuestion($testQuestion)) !== true) {
            return $response;
        } else {
            if (!$this->checkLinkExists($question, $attachment)) {
                return Response::make('attachment not found', 404);
            }

            return Response::make($attachment, 200);
        }
    }

    /**
     * Update the specified attachment in storage.
     *
     * @param  Attachment $attachment
     * @param UpdateAttachmentRequest $request
     * @return Response
     */
    public function update(TestQuestion $testQuestion, Attachment $attachment, UpdateAttachmentRequest $request)
    {
        $question = $testQuestion->question;
        if (($response = $this->validateQuestion($question)) !== true) {
            return $response;
        } else {
            $attachment->fill($request->all());

            if ($attachment->isDirty() || $attachment->isDirtyFile()) {
                if (($questionDuplicated = $question->isUsed($testQuestion))) {
                    $question = $question->duplicate([], $attachment);
                    if ($question === false) {
                        return Response::make('Failed to duplicate question', 500);
                    }

                    $attachment = $attachment->duplicate($question, $request->all());
                    if ($attachment === false) {
                        return Response::make('Failed to duplicate and update attachment', 500);
                    }

                    if (!QuestionAuthor::addAuthorToQuestion($question)) {
                        return Response::make('Failed to attach author to question', 500);
                    }

                    $testQuestion->setAttribute('question_id', $question->getKey());
                    if ($questionDuplicated && !$testQuestion->save()) {
                        return Response::make('Failed to update test question', 500);
                    } else {
                        $attachment->setAttribute('group_question_question_path', '');
                        return Response::make($attachment, 200);
                    }
                } else {
                    if (!QuestionAuthor::addAuthorToQuestion($question)) {
                        return Response::make('Failed to attach author to question', 500);
                    }

                    if ($question->attachments()->save($attachment) !== false) {
                        $attachment->setAttribute('group_question_question_path', '');
                        return Response::make($attachment, 200);
                    } else {
                        return Response::make('Failed to update attachment', 500);
                    }
                }
            } else {
                $attachment->setAttribute('group_question_question_path', '');
                return Response::make($attachment, 200);
            }
        }
    }

    /**
     * Remove the specified attachment from storage.
     *
     * @param  Attachment  $attachment
     * @return Response
     */
    public function destroy(TestQuestion $testQuestion, Attachment $attachment)
    {
        $question = $testQuestion->question;
        if (($response = $this->validateQuestion($question)) !== true) {
            return $response;
        } else {
            if (!$this->checkLinkExists($question, $attachment)) {
                return Response::make('Attachment not found', 404);
            }

            if ($question->isUsed($testQuestion)) {
                $question = $question->duplicate([], $attachment);
                if ($question === false) {
                    return Response::make('Failed to duplicate question', 500);
                }

                if (!QuestionAuthor::addAuthorToQuestion($question)) {
                    return Response::make('Failed to attach author to question', 500);
                }

                $testQuestion->setAttribute('question_id', $question->getKey());
                if (!$testQuestion->save()) {
                    return Response::make('Failed to update test question', 500);
                } else {
                    $attachment->setAttribute('group_question_question_path', '');
                    return Response::make($attachment, 200);
                }
            } else {
                if (!QuestionAuthor::addAuthorToQuestion($question)) {
                    return Response::make('Failed to attach author to question', 500);
                }

                $questionAttachment = $question->questionAttachments()->where('attachment_id', $attachment->getKey())->first();
                if (!$questionAttachment->delete()) {
                    return Response::make('Failed to delete question attachment', 500);
                }

                if ($attachment->isUsed($questionAttachment)) {
                    $attachment->setAttribute('group_question_question_path', '');
                    return Response::make($attachment, 200);
                } else {
                    if ($attachment->delete() ) {
                        $attachment->setAttribute('group_question_question_path', '');
                        return Response::make($attachment, 200);
                    } else {
                        return Response::make('Failed to delete attachment', 500);
                    }
                }
            }
        }
    }

    /**
     * Offers a download to the specified attachment from storage.
     *
     * @param  Attachment  $attachment
     * @return Response
     */
    public function download(TestQuestion $testQuestion, Attachment $attachment)
    {
        $question = $testQuestion->question;
        if (($response = $this->validateQuestion($question)) !== true) {
            return $response;
        } else {
            if ($attachment->question_id !== $question->getKey()) {
                return Response::make('Attachment not found', 404);
            }

            return Response::download($attachment->getCurrentPath(), $attachment->getAttribute('file_name', null));
        }
    }

    /**
     * Perform pre-action checks
     * @param TestQuestion $question
     * @return bool
     */
    protected function validateQuestion($question) {
        if (!method_exists($question, 'attachments')) {
            return Response::make('Question does not allow attachments.', 404);
        }

        return true;
    }

    protected function checkLinkExists($question, $attachment) {
        return ($question->questionAttachments()->withTrashed()->where('attachment_id', $attachment->getKey())->count() > 0);
    }
}

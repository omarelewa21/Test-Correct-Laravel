<?php namespace tcCore\Http\Controllers\GroupQuestionQuestions;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\Attachment;
use tcCore\Http\Requests\CreateAttachmentRequest;
use tcCore\Http\Requests\UpdateAttachmentRequest;
use tcCore\Lib\GroupQuestionQuestion\GroupQuestionQuestionManager;
use tcCore\QuestionAttachment;
use tcCore\QuestionAuthor;
use tcCore\GroupQuestionQuestion;

class AttachmentsController extends Controller {

    /**
     * Display a listing of the attachments.
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
    public function store(GroupQuestionQuestionManager $groupQuestionQuestionManager, CreateAttachmentRequest $request)
    {
        $groupQuestionQuestion = $groupQuestionQuestionManager->getQuestionLink();
        $question = $groupQuestionQuestion->question;
        if (($response = $this->validateQuestion($question)) !== true) {
            return $response;
        } else {
            $attachment = new Attachment();

            $attachment->fill($request->all());

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

            if ($attachment->save() === false) {
                return Response::make('Failed to create attachment', 500);
            }

            $questionAttachment = new QuestionAttachment();
            $questionAttachment->setAttribute('question_id', $question->getKey());
            $questionAttachment->setAttribute('attachment_id', $attachment->getKey());

            if($questionAttachment->save()) {
                $attachment->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());
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
    public function show(GroupQuestionQuestionManager $groupQuestionQuestionManager, Attachment $attachment)
    {
        $groupQuestionQuestion = $groupQuestionQuestionManager->getQuestionLink();
        $question = $groupQuestionQuestion->question;
        if (($response = $this->validateQuestion($groupQuestionQuestion)) !== true) {
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
    public function update(GroupQuestionQuestionManager $groupQuestionQuestionManager, Attachment $attachment, UpdateAttachmentRequest $request)
    {
        $groupQuestionQuestion = $groupQuestionQuestionManager->getQuestionLink();
        $question = $groupQuestionQuestion->question;
        if (($response = $this->validateQuestion($question)) !== true) {
            return $response;
        } else {
            $attachment->fill($request->all());

            if ($attachment->isDirty() || $attachment->isDirtyFile()) {
                if (($questionDuplicated = $question->isUsed($groupQuestionQuestion))) {
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

                    $groupQuestionQuestion->setAttribute('question_id', $question->getKey());
                    if ($questionDuplicated && !$groupQuestionQuestion->save()) {
                        return Response::make('Failed to update group question question', 500);
                    } else {
                        $attachment->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());
                        return Response::make($attachment, 200);
                    }
                } else {
                    if (!QuestionAuthor::addAuthorToQuestion($question)) {
                        return Response::make('Failed to attach author to question', 500);
                    }

                    if ($question->attachments()->save($attachment) !== false) {
                        $attachment->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());
                        return Response::make($attachment, 200);
                    } else {
                        return Response::make('Failed to update attachment', 500);
                    }
                }
            } else {
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
    public function destroy(GroupQuestionQuestionManager $groupQuestionQuestionManager, Attachment $attachment)
    {
        $groupQuestionQuestion = $groupQuestionQuestionManager->getQuestionLink();
        $question = $groupQuestionQuestion->question;
        if (($response = $this->validateQuestion($question)) !== true) {
            return $response;
        } else {
            if (!$this->checkLinkExists($question, $attachment)) {
                return Response::make('Attachment not found', 404);
            }

            if ($question->isUsed($groupQuestionQuestion)) {
                $question = $question->duplicate([], $attachment);
                if ($question === false) {
                    return Response::make('Failed to duplicate question', 500);
                }

                if (!QuestionAuthor::addAuthorToQuestion($question)) {
                    return Response::make('Failed to attach author to question', 500);
                }

                $groupQuestionQuestion->setAttribute('question_id', $question->getKey());
                if (!$groupQuestionQuestion->save()) {
                    return Response::make('Failed to update group question question', 500);
                } else {
                    $attachment->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());
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
                    return Response::make($attachment, 200);
                } else {
                    if ($attachment->delete() ) {
                        $attachment->setAttribute('group_question_question_path', $groupQuestionQuestionManager->getGroupQuestionQuestionPath());
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
    public function download(GroupQuestionQuestionManager $groupQuestionQuestionManager, Attachment $attachment)
    {
        $groupQuestionQuestion = $groupQuestionQuestionManager->getQuestionLink();
        $question = $groupQuestionQuestion->question;
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
     * @param GroupQuestionQuestion $question
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

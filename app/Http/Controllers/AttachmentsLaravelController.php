<?php namespace tcCore\Http\Controllers;

use Illuminate\Support\Facades\Response;
use tcCore\Answer;
use tcCore\Attachment;
use tcCore\Question;
use tcCore\QuestionAttachment;

class AttachmentsLaravelController extends Controller
{

    /**
     * Display the specified attachment.
     *
     * @param Attachment $attachment
     * @param Answer $answer
     * @return \Illuminate\Http\Response|\Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function show(Attachment $attachment, Answer $answer)
    {
        if ($attachment->isAccessableFrom($answer)) {
            return Response::file($attachment->getCurrentPath());
        }
        return Response::noContent();
    }

    public function showPreview(Attachment $attachment, Question $question)
    {
        if (!QuestionAttachment::whereAttachmentId($attachment->getKey())->whereQuestionId($question->getKey())->exists()) {
            return Response::noContent();
        }

        if(!file_exists($attachment->getCurrentPath())) {
            return Response::noContent();
        }

        return Response::file($attachment->getCurrentPath());
    }
}

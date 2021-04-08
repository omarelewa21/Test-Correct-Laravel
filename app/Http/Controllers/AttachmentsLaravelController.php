<?php namespace tcCore\Http\Controllers;

use Illuminate\Support\Facades\Response;
use tcCore\Answer;
use tcCore\Attachment;

class AttachmentsLaravelController extends Controller {

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

    public function showPreview(Attachment $attachment, $question)
    {
        if ($attachment->questions()->value('uuid') === $question->uuid) {
            return Response::file($attachment->getCurrentPath());
        }
        return Response::noContent();
    }
}

<?php namespace tcCore\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use tcCore\Answer;
use tcCore\Attachment;
use tcCore\TestParticipant;

class AttachmentsLaravelController extends Controller {

    /**
     * Display the specified attachment.
     *
     * @param  Attachment  $attachment
     * @return Response
     */
    public function show(Attachment $attachment, Answer $answer)
    {
        if ($attachment->canBeAccessedByUser(Auth::user(), $answer->getKey())) {
            return Response::file($attachment->getCurrentPath());
        }
        return Response::noContent();
//        return Response::make('Not allowed to access this attachment',403);
    }
}

<?php namespace tcCore\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use tcCore\Attachment;
use tcCore\TestParticipant;

class AttachmentsLaravelController extends Controller {

    /**
     * Display the specified attachment.
     *
     * @param  Attachment  $attachment
     * @return Response
     */
    public function show(Attachment $attachment)
    {
        if ($attachment->canBeAccessedByUser(Auth::user())) {
            return Response::file($attachment->getCurrentPath());
        }

        return Response::noContent();;
    }
}

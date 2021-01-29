<?php namespace tcCore\Http\Controllers;

use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\Attachment;

class AttachmentsLaravelController extends Controller {

    /**
     * Display the specified attachment.
     *
     * @param  Attachment  $attachment
     * @return Response
     */
    public function show(Attachment $attachment)
    {
        if ($attachment->type == 'video')
        {
            return $attachment->link;
        }

        return Response::file($attachment->getCurrentPath());
    }
}

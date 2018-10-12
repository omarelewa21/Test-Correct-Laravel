<?php namespace tcCore\Http\Controllers;

use Illuminate\Support\Facades\Response;
use tcCore\Http\Requests;
use tcCore\Http\Controllers\Controller;
use tcCore\Attachment;

class AttachmentsController extends Controller {

    /**
     * Display the specified attachment.
     *
     * @param  Attachment  $attachment
     * @return Response
     */
    public function show(Attachment $attachment)
    {
        return Response::make($attachment, 200);
    }

    /**
     * Offers a download to the specified attachment from storage.
     *
     * @param  Attachment  $attachment
     * @return Response
     */
    public function download(Attachment $attachment)
    {
        return Response::download($attachment->getCurrentPath(), $attachment->getAttribute('file_name', null));
    }


}

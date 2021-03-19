<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Attachment;

class PdfAttachmentsLaravelController extends Controller
{

    public function show(Attachment $attachment)
    {
        $attachment_url = route('student.question-attachment-show', $attachment->getKey(), false);
        $is_question_pdf = 1;
        return view('components.attachment.pdf-attachment', compact(['attachment_url', 'is_question_pdf']));

    }
}

<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Answer;
use tcCore\Attachment;

class PdfAttachmentsLaravelController extends Controller
{

    public function show(Attachment $attachment, Answer $answer)
    {
        $attachment_url = route('student.question-attachment-show', ['attachment' => $attachment->uuid, 'answer' => $answer->uuid], false);
        $is_question_pdf = 1;
        return view('components.attachment.pdf-attachment', compact(['attachment_url', 'is_question_pdf']));

    }

    public function showPreview(Attachment $attachment, $question)
    {
        $attachment_url = route('teacher.preview.question-attachment-show', ['attachment' => $attachment->uuid, 'question' => $question->uuid], false);
        $is_question_pdf = 1;
        return view('components.attachment.pdf-attachment', compact(['attachment_url', 'is_question_pdf']));
    }
}

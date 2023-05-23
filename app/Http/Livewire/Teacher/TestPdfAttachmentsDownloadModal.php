<?php

namespace tcCore\Http\Livewire\Teacher;

use tcCore\Http\Livewire\TCModalComponent;
use tcCore\Question;
use tcCore\Test;

class TestPdfAttachmentsDownloadModal extends TCModalComponent
{
    public string $uuid;
    public $test;
    protected $pdfAttachments;

    public $attachment;
    public $question;

    public bool $displayValueRequiredMessage = false;
    protected static array $maxWidths = [
        'w-modal' => 'max-w-[720px]',
    ];

    public function mount($test)
    {
        $this->uuid = $test;

    }

    public function getRoute($data)
    {
        return route('teacher.preview.question-pdf-attachment-show', ['attachment'=> $data[0], 'question' => $data[1]]);
    }

    private function getPdfAttachments()
    {
        $this->pdfAttachments = $this->test->pdfAttachments;

        $this->pdfAttachments->map(function ($attachment) {
            $attachment->questionUuid = Question::find($attachment->pivot->question_id)->uuid;
            return $attachment;
        });
    }

    public static function modalMaxWidth(): string
    {
        return 'w-modal';
    }

    public function render()
    {
        $this->test = Test::findByUuid($this->uuid);

        $this->getPdfAttachments();

        return view('livewire.teacher.test-pdf-attachments-download-modal');
    }

    public function close()
    {
        $this->closeModal();
    }
}

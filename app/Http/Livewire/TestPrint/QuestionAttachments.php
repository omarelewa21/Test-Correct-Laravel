<?php

namespace tcCore\Http\Livewire\TestPrint;

use Livewire\Component;

class QuestionAttachments extends Component
{
    public $attachments;
    public $attachment_counters;

    public function mount()
    {
        $this->prepareAttachments();
    }

    public function render()
    {
        return view('livewire.test_print.question-attachments');
    }

    protected function prepareAttachments()
    {
        $this->attachments = $this->attachments->map(function ($attachment) {
            $attachment->filetype = $attachment->getFileType();
            return $attachment;
        });
    }
}

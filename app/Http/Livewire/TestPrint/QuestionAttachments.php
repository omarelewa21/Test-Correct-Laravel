<?php

namespace tcCore\Http\Livewire\TestPrint;

use Livewire\Component;

class QuestionAttachments extends Component
{
    public $attachments;

    public function mount()
    {
        $this->filterAndSortAttachments();
    }

    public function render()
    {
        return view('livewire.test_print.question-attachments');
    }

    private function filterAndSortAttachments()
    {
        $this->attachments = $this->attachments->map(function ($attachment) {
            $attachment->filetype = $attachment->getFileType();
            return $attachment;
        })->reject(function ($attachment) {
            return $attachment->filetype == 'pdf';
        })->sortBy('filetype');
    }
}

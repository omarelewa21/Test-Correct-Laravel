<?php

namespace tcCore\Http\Livewire\Teacher;

use LivewireUI\Modal\ModalComponent;
use tcCore\Question;

class QuestionCmsPreviewModal extends ModalComponent
{
    public $question;

    public function mount($uuid)
    {
        $this->question = Question::whereUuid($uuid)->first();
    }

    public function render()
    {
        return view('livewire.teacher.question-cms-preview-modal');
    }
}
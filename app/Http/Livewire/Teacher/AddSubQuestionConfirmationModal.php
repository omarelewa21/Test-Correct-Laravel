<?php

namespace tcCore\Http\Livewire\Teacher;

use LivewireUI\Modal\ModalComponent;
use tcCore\Question;

class AddSubQuestionConfirmationModal extends ModalComponent
{
    public string $questionUuid;

    public function mount($questionUuid)
    {
        $this->questionUuid = $questionUuid;
    }

    public function render()
    {
        return view('livewire.teacher.add-sub-question-confirmation-modal');
    }
}

<?php

namespace tcCore\Http\Livewire\Teacher;

use LivewireUI\Modal\ModalComponent;
use tcCore\Http\Livewire\TCModalComponent;
use tcCore\Question;

class AddSubQuestionConfirmationModal extends TCModalComponent
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

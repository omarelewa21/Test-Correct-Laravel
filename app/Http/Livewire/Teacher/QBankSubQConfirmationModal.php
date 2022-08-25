<?php

namespace tcCore\Http\Livewire\Teacher;

use LivewireUI\Modal\ModalComponent;
use tcCore\Question;

class QBankSubQConfirmationModal extends ModalComponent
{
    public int $questionId;

    public function mount($questionUuid)
    {
        $this->questionId = Question::whereUuid($questionUuid)->value('id');
    }

    public function addQuestionToTest()
    {
        $this->emit('addQuestionFromDetail', $this->questionId);
    }

    public function render()
    {
        return view('livewire.teacher.q-bank-sub-q-confirmation-modal');
    }
}

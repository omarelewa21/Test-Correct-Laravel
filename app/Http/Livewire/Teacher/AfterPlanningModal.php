<?php

namespace tcCore\Http\Livewire\Teacher;

use LivewireUI\Modal\ModalComponent;
use tcCore\TestTake;

class AfterPlanningModal extends ModalComponent
{
    public $testTake;

    public function mount($testTakeUuid)
    {
        $this->testTake = TestTake::whereUuid($testTakeUuid)->with('test')->first();
    }

    public function render()
    {
        return view('livewire.teacher.after-planning-modal');
    }

    public function forceCloseModal()
    {
        $this->forceClose()->closeModal();
    }
}

<?php

namespace tcCore\Http\Livewire\Teacher;

use LivewireUI\Modal\ModalComponent;
use tcCore\TestTake;

class AfterPlanningModal extends ModalComponent
{
    public $testTake;

    public function mount(TestTake $testTake)
    {
        $this->testTake = $testTake->load('test');
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

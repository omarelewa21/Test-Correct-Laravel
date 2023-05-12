<?php

namespace tcCore\Http\Livewire\Student;

use tcCore\Http\Livewire\TCComponent;

class IntenseObserver extends TCComponent
{
    public $deviceId;

    public $sessionId;

    public function render()
    {
        return view('livewire.student.intense-observer');
    }
}

<?php

namespace tcCore\Http\Livewire\Student;

use Livewire\Component;

class IntenseObserver extends Component
{
    public $deviceId;

    public $sessionId;

    public function render()
    {
        return view('livewire.student.intense-observer');
    }
}

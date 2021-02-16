<?php

namespace tcCore\Http\Livewire\Student;

use Livewire\Component;

class FraudDetection extends Component
{
    public $fraudDetected = false;

    public function render()
    {
        return view('components.fraud-detected');
    }

    public function updatedFraudDetected($value)
    {
        // db query?
    }
}

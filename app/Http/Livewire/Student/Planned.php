<?php

namespace tcCore\Http\Livewire\Student;

use Livewire\Component;
use tcCore\Http\Traits\WithPersonalizedTestTakes;

class Planned extends Component
{
    use WithPersonalizedTestTakes;

    public function render()
    {
        return view('plan-test-take', ['testTakes' => $this->fetchTestTakes()])->layout('layouts.student');
    }
}

<?php

namespace tcCore\Http\Livewire\CoLearning;

use tcCore\Http\Livewire\TCComponent;

class Header extends TCComponent
{
    public $testName;

    public function back()
    {
        return redirect()->route('student.test-takes', ['tab' => 'discuss']);
    }

    public function render()
    {
        return view('livewire.co-learning.header');
    }

}

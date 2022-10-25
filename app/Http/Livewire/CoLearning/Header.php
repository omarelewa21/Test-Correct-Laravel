<?php

namespace tcCore\Http\Livewire\CoLearning;

use Livewire\Component;

class Header extends Component
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

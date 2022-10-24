<?php

namespace tcCore\Http\Livewire\CoLearning;

use Livewire\Component;

class Header extends Component
{
    public $testName;

    public function back()
    {
        return back();
    }

    public function render()
    {
        return view('livewire.co-learning.header');
    }

}

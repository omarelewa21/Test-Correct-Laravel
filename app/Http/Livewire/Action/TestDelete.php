<?php

namespace tcCore\Http\Livewire\Action;

use Livewire\Component;
use tcCore\Test;

class TestDelete extends Component
{
    public $test;

    public function mount($testUuid)
    {
        $this->test = Test::findByUuid($testUuid);

    }


    public function render()
    {
        return view('livewire.action.test-delete');
    }
}

<?php

namespace tcCore\Http\Livewire\Teacher;

use Livewire\Component;
use tcCore\Test;

class CopyTestFromSchoollocationModal extends Component
{
    public $showModal = false;

    public $test;

    protected $listeners = ['showModal'];

    public function mount(){
        $this->test = new Test;
    }

    public function showModal($testUuid)
    {
        $this->test = \tcCore\Test::whereUuid($testUuid)->first();

        $this->showModal = true;
    }


    public function render()
    {
        return view('livewire.teacher.copy-test-from-schoollocation-modal');
    }
}

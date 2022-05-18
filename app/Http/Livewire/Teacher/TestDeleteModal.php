<?php

namespace tcCore\Http\Livewire\Teacher;

use Livewire\Component;

class TestDeleteModal extends Component
{
    protected $listeners = ['displayModal'];

    public $uuid = '';

    public $showModal = false;

    public function render()
    {
        return view('livewire.teacher.test-delete-modal')->with(['title'=> 'hier']);
    }

    public function displayModal($testUuid)
    {
        $this->uuid = $testUuid;

        $this->showModal = true;
    }

    public function removeTest()
    {
        $test = \tcCore\Test::whereUuid($this->uuid)->first();

        $this->showModal = false;
    }
}

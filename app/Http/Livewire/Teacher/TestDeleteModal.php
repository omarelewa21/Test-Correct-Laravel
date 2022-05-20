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

    public function deleteTest()
    {
        // @TODO needs some rules if we can delete this test;
        $test = \tcCore\Test::whereUuid($this->uuid)->delete();

        $this->showModal = false;
        $this->emitUp('test-deleted');
    }
}

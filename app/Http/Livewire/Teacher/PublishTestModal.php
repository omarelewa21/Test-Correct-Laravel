<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Support\Facades\Auth;
use LivewireUI\Modal\ModalComponent;
use tcCore\Test;

class PublishTestModal extends ModalComponent
{
    public $testUuid;
    public $showInfo;

    public function mount($testUuid)
    {
        $this->testUuid = $testUuid;
        $this->showInfo = !Auth::user()->has_published_test;
    }

    public function render()
    {
        return view('livewire.teacher.publish-test-modal');
    }

    public function handle()
    {
        Test::findByUuid($this->testUuid)->publish()->save();
        Auth::user()->has_published_test = true;

        $this->emit('test-updated');

        $this->closeModal();
    }
}

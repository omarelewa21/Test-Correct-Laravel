<?php

namespace tcCore\Http\Livewire\Teacher;

use LivewireUI\Modal\ModalComponent;
use tcCore\TestTake;
use tcCore\TemporaryLogin;

class AfterPlanningModal extends ModalComponent
{
    public $testTake;
    public $testTakeLink;

    public function mount($testTakeUuid)
    {
        $this->testTake = TestTake::whereUuid($testTakeUuid)->with('test')->first();

        $this->testTakeLink = config('app.base_url') ."directlink/". $testTakeUuid;
    }

    public function render()
    {
        return view('livewire.teacher.after-planning-modal');
    }

    public function forceCloseModal()
    {
        $this->forceClose()->closeModal();
    }

    public function toPlannedTest()
    {
        $url = sprintf("test_takes/view/%s", $this->testTake->uuid);
        $options = TemporaryLogin::buildValidOptionObject('page', $url);
        return auth()->user()->redirectToCakeWithTemporaryLogin($options);
    }
}

<?php

namespace tcCore\Http\Livewire\Student;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use tcCore\TestParticipant;
use tcCore\TestTakeEvent;
use tcCore\TestTakeStatus;

class FraudDetection extends Component
{
    public $fraudDetected = false;
    public $testParticipantId, $testParticipantUuid, $testTakeUuid;

//    protected $listeners = ['setFraudDetected'];
    protected function getListeners() {
        return [
            'echo-private:TestParticipant.'.$this->testParticipantUuid.',.RemoveFraudDetectionNotification' => 'isTestTakeEventConfirmed',
            'setFraudDetected' => 'shouldDisplayFraudMessage'
        ];
    }

    public $testTakeEvents;

    public function render()
    {
        return view('components.fraud-detected');
    }

    public function setFraudDetected()
    {
        $this->dispatchBrowserEvent('set-red-header-border');
    }

    public function removeFraudDetected()
    {
        $this->dispatchBrowserEvent('remove-red-header-border');
    }

    public function isTestTakeEventConfirmed()
    {
        $this->shouldDisplayFraudMessage();
        if (!$this->canParticipantContinue()) {
            $this->emitTo('student.test-take', 'set_force_taken_away');
        }
    }

    public function shouldDisplayFraudMessage()
    {
        $this->fraudDetected = TestTakeEvent::hasFraudBeenDetectedForParticipant($this->testParticipantId);
        $this->fraudDetected ? $this->setFraudDetected() : $this->removeFraudDetected();
    }

    public function canParticipantContinue(): bool
    {
        return TestParticipant::whereId($this->testParticipantId)->value('test_take_status_id') == TestTakeStatus::STATUS_TAKING_TEST;
    }
}

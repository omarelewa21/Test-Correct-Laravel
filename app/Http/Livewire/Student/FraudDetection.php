<?php

namespace tcCore\Http\Livewire\Student;

use Livewire\Component;
use tcCore\TestParticipant;
use tcCore\TestTakeEvent;
use tcCore\TestTakeStatus;

class FraudDetection extends Component
{
    public $fraudDetected = false;
    public $testParticipantId;

    protected $listeners = ['setFraudDetected', 'setFraudDetected'];

    public $testTakeEvents;

    public function mount()
    {
        $this->shouldDisplayFraudMessage();
    }

    public function render()
    {
        return view('components.fraud-detected');
    }

    public function setFraudDetected()
    {
        $this->fraudDetected = true;
        $this->dispatchBrowserEvent('set-red-header-border');
    }

    public function isTestTakeEventConfirmed()
    {
        $this->shouldDisplayFraudMessage();
        if (!$this->canParticipantContinue()) {
            $this->emitTo('student.test-take', 'set_force_taken_away');
        }
    }

    private function shouldDisplayFraudMessage()
    {
        $this->fraudDetected = !!TestTakeEvent::leftJoin('test_take_event_types', 'test_take_events.test_take_event_type_id', '=', 'test_take_event_types.id')
            ->where('confirmed', 0)
            ->where('test_participant_id', $this->testParticipantId)
            ->where('requires_confirming', 1)
            ->count();
        if (!$this->fraudDetected) {
            $this->dispatchBrowserEvent('remove-red-header-border');
        }
    }

    public function canParticipantContinue(): bool
    {
        return TestParticipant::whereId($this->testParticipantId)->value('test_take_status_id') == TestTakeStatus::STATUS_TAKING_TEST;
    }
}

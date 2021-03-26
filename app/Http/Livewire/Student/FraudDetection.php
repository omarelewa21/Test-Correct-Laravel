<?php

namespace tcCore\Http\Livewire\Student;

use Livewire\Component;
use tcCore\TestTakeEvent;
use tcCore\TestTakeEventType;

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
    }

    public function isTestTakeEventConfirmed()
    {
        $this->shouldDisplayFraudMessage();
    }

    private function shouldDisplayFraudMessage()
    {
        // select count(*) from test_take_events left join test_take_event_types on (test_take_events.test_take_event_type_id = test_take_event_types.id) where test_take_event_types.requires_confirming=1 and test_participant_id =251 and test_take_events.confirmed = 0

        $this->fraudDetected = !! TestTakeEvent::leftJoin('test_take_event_types', 'test_take_events.test_take_event_type_id', '=', 'test_take_event_types.id')
            ->where('confirmed' , 0)
            ->where('test_participant_id', $this->testParticipantId)
            ->where('requires_confirming', 1)
            ->count();
    }
}

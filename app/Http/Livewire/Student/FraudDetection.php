<?php

namespace tcCore\Http\Livewire\Student;

use Livewire\Component;
use tcCore\TestTakeEvent;
use tcCore\TestTakeEventType;

class FraudDetection extends Component
{
    public $fraudDetected = false;
    public $testParticipant;
    public $testTakeUuid;
    public $testTake;

    public function mount()
    {
        $this->testTake = \tcCore\TestTake::whereUuid($this->testTakeUuid)->first();

        $testTakeEvents = TestTakeEvent::where('test_participant_id', $this->testParticipant->id)->get();
        if (!$testTakeEvents->isEmpty()) {
            foreach($testTakeEvents as $event){
                if ($event->testTakeEventType->requires_confirming) {
                    $this->fraudDetected = true;
                }
            }
        }
    }

    public function render()
    {
        return view('components.fraud-detected');
    }

    public function createTestTakeEvent($event)
    {
        $testTakeEvent = new TestTakeEvent([
            'test_participant_id' => $this->testParticipant->id,
            'test_take_event_type_id' => $this->getEventTypeId($event)
        ]);

        $this->testTake->testTakeEvents()->save($testTakeEvent);

        $this->fraudDetected = true;
    }

    private function getEventTypeId($event)
    {
        return TestTakeEventType::whereReason($event)->first()->id;
    }
}

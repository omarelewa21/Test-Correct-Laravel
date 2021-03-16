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

    protected $listeners = ['setFraudDetected', 'setFraudDetected'];

    public function mount()
    {
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

    public function setFraudDetected()
    {
        $this->fraudDetected = true;
    }
}

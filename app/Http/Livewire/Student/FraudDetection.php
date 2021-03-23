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

    public $testTakeEvents;

    public function mount()
    {
        $this->shouldDisplayFraudMessage();
    }

    public function render()
    {
        if (session()->pull('redirectFromDLL')) {
            $this->redirect(config('app.url_login'));
        }
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
        $this->testTakeEvents = TestTakeEvent::where('test_participant_id', $this->testParticipant->id)->get();
        if (!$this->testTakeEvents->isEmpty()) {
            foreach ($this->testTakeEvents as $event) {
                if ($event->testTakeEventType->requires_confirming && !$event->confirmed) {
                    $this->fraudDetected = true;
                } else {
                    $this->fraudDetected = false;
                }
            }
        }
    }
}

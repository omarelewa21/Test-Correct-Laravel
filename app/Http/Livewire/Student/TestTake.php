<?php

namespace tcCore\Http\Livewire\Student;

use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use tcCore\TemporaryLogin;
use tcCore\TestParticipant;
use tcCore\TestTakeEvent;
use tcCore\TestTakeEventType;


class TestTake extends Component
{
    public $testTakeUuid;
    public $showTurnInModal = false;
    public $testParticipantId;
    public $forceTakenAwayModal = false;

    /** @var int
     *  time in milliseconds a notification is shown
     */
    public $notificationTimeout = 5000;
    protected $listeners = ['set-force-taken-away' => 'setForceTakenAway'];

    public function render()
    {
        return view('livewire.student.test-take');
    }

    public function turnInModal()
    {
        $this->showTurnInModal = true;
    }

    public function TurnInTestTake()
    {
        $testParticipant = TestParticipant::whereId($this->testParticipantId)->first();

        if (!$testParticipant->handInTestTake()) {
//            @TODO make error handling on failed hand in
            //error handling
        }

        $temporaryLogin = TemporaryLogin::create(
            ['user_id' => $testParticipant->user_id]
        );
        $redirectUrl = $temporaryLogin->createCakeUrl();

        session()->flush();
        return redirect()->to($redirectUrl);
    }

    public function createTestTakeEvent($event)
    {
        $eventType = $this->getEventType($event);
        $testTakeEvent = new TestTakeEvent([
            'test_participant_id' => $this->testParticipantId,
            'test_take_event_type_id' => $eventType->getKey(),
        ]);

        if ($eventType->requires_confirming) {
            $this->emitTo('student.fraud-detection', 'setFraudDetected');
        }

        \tcCore\TestTake::whereUuid($this->testTakeUuid)->first()->testTakeEvents()->save($testTakeEvent);
    }

    private function getEventType($event)
    {
        return TestTakeEventType::whereReason($event)->first();
    }

    public function setForceTakenAway()
    {
        $this->forceTakenAwayModal = true;
    }
}

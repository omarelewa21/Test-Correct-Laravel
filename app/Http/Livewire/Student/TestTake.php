<?php

namespace tcCore\Http\Livewire\Student;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\TemporaryLogin;
use tcCore\TestParticipant;
use tcCore\TestTakeEvent;
use tcCore\TestTakeEventType;


class TestTake extends Component
{
    const FALLBACK_EVENT_TYPE_ID = 3; //lost-focus
    public $testTakeUuid;
    public $showTurnInModal = false;
    public $testParticipantId;
    public $testParticipantUuid;
    public $forceTakenAwayModal = false;
    public $browserTestingDisabledModal = false;

    /** @var int
     *  time in milliseconds a notification is shown
     */
    public $notificationTimeout = 5000;

    protected function getListeners()
    {
        return [
            'set-force-taken-away'                                                                                  => 'setForceTakenAway',
            'checkConfirmedEvents'                                                                                  => 'checkConfirmedEvents',
            'echo-private:TestParticipant.' . $this->testParticipantUuid . ',.TestTakeForceTakenAway'               => 'setForceTakenAway',
            'echo-private:TestParticipant.' . $this->testParticipantUuid . ',.TestTakeReopened'                     => 'testTakeReopened',
            'echo-private:TestParticipant.' . $this->testParticipantUuid . ',.BrowserTestingDisabledForParticipant' => 'checkIfParticipantCanContinueWithoutApp',
            'studentInactive'                                                                                       => 'handleInactiveStudent'
        ];
    }

    public function render()
    {
        return view('livewire.student.test-take');
    }

    public function turnInModal()
    {
        $this->showTurnInModal = true;
    }

    public function TurnInTestTake($forceTaken = false)
    {
        $testParticipant = TestParticipant::whereId($this->testParticipantId)->first();

        if (!$testParticipant->handInTestTake()) {
//            @TODO make error handling on failed hand in
            //error handling
        }

        // @TODO move this to returnToDashboard when all students use new env
        if (Auth::user()->guest) {
            $routeParameters = $this->getRouteParametersForGuest($forceTaken);
            return redirect(route('auth.login', $routeParameters));
        }
        $this->returnToDashboard();
    }

    public function createTestTakeEventEvent($event)
    {
        $eventType = $this->getEventType($event);
        $testTakeEvent = new TestTakeEvent([
            'test_participant_id'     => $this->testParticipantId,
            'test_take_event_type_id' => $eventType->getKey(),
        ]);

        $currentTestTake = \tcCore\TestTake::whereUuid($this->testTakeUuid)->first();
        $participant = TestParticipant::find($this->testParticipantId);


        if ($currentTestTake && $participant->shouldFraudNotificationsBeShown()) {
            // turn off fraud detection;
            if ($eventType->requires_confirming) {
                $this->emitTo('student.fraud-detection', 'setFraudDetected');
            }
            $currentTestTake->testTakeEvents()->save($testTakeEvent);
        }
    }

    private function getEventType($event)
    {
        $eventType = TestTakeEventType::whereReason($event)->first();
        if ($eventType === null) {
            return TestTakeEventType::find(self::FALLBACK_EVENT_TYPE_ID);
        }
        return $eventType;
    }

    public function setForceTakenAway($eventData = null)
    {
        $this->dispatchBrowserEvent('force-taken-away-blur', ['shouldBlur' => true]);
        $this->forceTakenAwayModal = true;
    }

    public function checkConfirmedEvents($reason)
    {
        $eventConfirmed = TestTakeEventType::whereReason($reason)
            ->first()
            ->testTakeEvents()
            ->where('test_participant_id', $this->testParticipantId)
            ->where('created_at', '>', Carbon::now()->subMinutes(3))
            ->latest()
            ->value('confirmed');

        if ($eventConfirmed == 1) {
            $this->createTestTake($reason);
        }
    }

    public function testTakeReopened($eventData)
    {
        $this->dispatchBrowserEvent('force-taken-away-blur', ['shouldBlur' => false]);
        $this->forceTakenAwayModal = false;
    }

    public function handleInactiveStudent()
    {
        Auth::logout();
        session()->invalidate();
        session()->regenerateToken();

        $this->redirect(BaseHelper::getLoginUrl());
    }

    private function browserTestingIsDisabled()
    {
        if (Auth::user()->guest) {
            $parameters = [
                'login_tab'          => 2,
                'guest_message_type' => 'error',
                'guest_message'      => 'no_browser_testing'
            ];
            return redirect(route('auth.login', $parameters));
        }

        $options = TemporaryLogin::buildValidOptionObject(
            'notification',
            [__('student.browser_testing_disabled_notification') => 'error']
        );
        $this->returnToDashboard($options);
    }

    public function checkIfParticipantCanContinueWithoutApp()
    {
        $participant = TestParticipant::findOrFail($this->testParticipantId);

        if (!$participant->canUseBrowserTesting() && $participant->isInBrowser()) {
            $this->browserTestingIsDisabled();
        }
    }

    public function returnToDashboard($options = null)
    {
        if (Auth::user()->schoolLocation->allow_new_student_environment) {
            return redirect(route('student.dashboard'));
        }

        Auth::user()->redirectToCakeWithTemporaryLogin($options);
    }

    private function getRouteParametersForGuest($forceTaken)
    {
        $parameters = [
            'login_tab'          => 2,
            'guest_message_type' => 'success',
            'guest_message'      => 'done_with_test'
        ];
        if ($forceTaken) {
            $parameters = [
                    'guest_message_type' => 'error',
                    'guest_message'      => 'removed_by_teacher'
                ] + $parameters;
        }

        if (session()->get('TLCOs', null) == 'iOS') {
            $parameters['device'] = 'ipad';
        }

        return $parameters;
    }

    public function shouldFraudNotificationsBeShown()
    {
        return ['shouldFraudNotificationsBeShown' => TestParticipant::find($this->testParticipantId)->shouldFraudNotificationsBeShown()];

    }

    public function showAssignmentElements()
    {
        $test = \tcCore\TestTake::whereUuid($this->testTakeUuid)
            ->select('id', 'test_id')
            ->with('test:id,test_kind_id')
            ->first()
            ->test;

        if ($test->isAssignment()) {
            $this->dispatchBrowserEvent('show-to-dashboard');
        }
    }
}

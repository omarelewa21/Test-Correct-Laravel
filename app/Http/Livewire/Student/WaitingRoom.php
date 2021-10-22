<?php

namespace tcCore\Http\Livewire\Student;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Livewire\Component;
use Ramsey\Uuid\Uuid;
use tcCore\Http\Traits\WithStudentTestTakes;
use tcCore\TemporaryLogin;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\TestTakeStatus;

class WaitingRoom extends Component
{
    use WithStudentTestTakes;

    protected function getListeners()
    {
        return [
            'start-test-take'                                                                                                  => 'startTestTake',
            'is-test-take-open'                                                                                                => 'isTestTakeOpen',
            'echo-private:TestParticipant.' . $this->testParticipant->getKey() . ',.TestTakeOpenForInteraction'                => 'isTestTakeOpen',
            'echo-private:TestParticipant.' . $this->testParticipant->getKey() . ',.InbrowserTestingUpdatedForTestParticipant' => 'participantAppCheck',
            'echo-private:TestParticipant.' . $this->testParticipant->getKey() . ',.RemoveParticipantFromWaitingRoom'          => 'removeParticipantFromWaitingRoom',
            //Presence channels are not completely working with Livewire listeners. Presence channel listener is located in x-init of this components blade file. -RR
//            'echo-presence:Presence-TestTake.' . $this->waitingTestTake->uuid . ',.TestTakeShowResultsChanged'          => 'isTestTakeOpen',
        ];
    }

    protected $queryString = ['take'];
    public $take;
    public $waitingTestTake;
    public $testParticipant;
    public $isTakeOpen;
    public $isTakeAlreadyTaken;
    public $countdownNumber = 3;
    public $testTakeStatusStage;
    public $meetsAppRequirement = true;
    public $participatingClasses = [];

    public function mount()
    {
        if (!isset($this->take) || !Uuid::isValid($this->take)) {
            return $this->escortUserFromWaitingRoom();
        }

        $this->waitingTestTake = $this->getWaitingRoomTestTake();
        $this->testParticipant = TestParticipant::whereUserId(Auth::id())->whereTestTakeId($this->waitingTestTake->getKey())->first();
        if (!$this->waitingTestTake || !$this->testParticipant) {
            return $this->escortUserFromWaitingRoom();
        }

        $this->testTakeStatusStage = $this->waitingTestTake->determineTestTakeStage();
        $this->participatingClasses = $this->getParticipatingClasses($this->waitingTestTake);

        $this->participantAppCheck();
    }

    public function render()
    {
        $this->waitingTestTake = $this->getWaitingRoomTestTake();
        return view('livewire.student.waiting-room')->layout('layouts.student');
    }

    public function startTestTake()
    {
        if ($this->waitingTestTake->test_take_status_id === TestTakeStatus::STATUS_TAKING_TEST) {
            $this->testParticipant->test_take_status_id = TestTakeStatus::STATUS_TAKING_TEST;
            $this->testParticipant->save();
        }

        $this->redirectRoute('student.test-take-laravel', $this->take);
    }

    public function isTestTakeOpen()
    {
        $stage = $this->testTakeStatusStage;
        $testParticipantStatus = TestParticipant::whereUserId(Auth::id())->whereTestTakeId($this->waitingTestTake->getKey())->value('test_take_status_id');
        $testTakeStatus = TestTake::whereUuid($this->take)->value('test_take_status_id');
        if ($stage === 'planned') {
            if ($testParticipantStatus > TestTakeStatus::STATUS_TAKING_TEST) {
                $this->isTakeOpen = false;
                return;
            }
            $this->isTakeOpen = $testTakeStatus == TestTakeStatus::STATUS_TAKING_TEST;
        }

        if ($stage === 'discuss') {
            $this->isTakeOpen = $testTakeStatus == TestTakeStatus::STATUS_DISCUSSING;
        }

        if ($stage === 'review') {
            $showResults = TestTake::whereUuid($this->take)->value('show_results');
            if ($showResults != null && $showResults->gt(Carbon::now())) {
                $this->isTakeOpen = $testTakeStatus == TestTakeStatus::STATUS_DISCUSSED;
            } else {
                $this->isTakeOpen = false;
            }
        }
        if ($stage === 'graded') {
            $showResults = TestTake::whereUuid($this->take)->value('show_results');
            if ($showResults != null && $showResults->gt(Carbon::now())) {
                $this->isTakeOpen = $testTakeStatus == TestTakeStatus::STATUS_RATED;
            } else {
                $this->isTakeOpen = false;
            }
        }
    }

    public function getCountdownNumber(): int
    {
        return $this->countdownNumber;
    }

    public function getWaitingRoomTestTake()
    {
        return TestTake::select('test_takes.*', 'subjects.name as subject_name', 'tests.name as test_name')
            ->join('tests', 'test_takes.test_id', '=', 'tests.id')
            ->join('subjects', 'tests.subject_id', '=', 'subjects.id')
            ->where('test_takes.id', TestTake::whereUuid($this->take)->value('id'))
            ->first();
    }

    public function startDiscussing()
    {
        $url = 'test_takes/discuss/' . $this->take;
        $options = TemporaryLogin::buildValidOptionObject('page', $url);

        Auth::user()->redirectToCakeWithTemporaryLogin($options);
    }

    public function startReview()
    {
        $url = 'test_takes/glance/' . $this->take;
        $options = TemporaryLogin::buildValidOptionObject('page', $url);

        Auth::user()->redirectToCakeWithTemporaryLogin($options);
    }

    public function returnToGuestChoicePage()
    {
        session()->flush();
        $this->testParticipant->available_for_guests = true;
        $this->testParticipant->save();

        session()->put('guest_take', $this->take);
        return redirect(route('guest-choice', ['take' => $this->take]));
    }

    public function participantAppCheck()
    {
        $this->meetsAppRequirement = !(!$this->testParticipant->canUseBrowserTesting() && $this->testParticipant->isInBrowser());
    }

    public function removeParticipantFromWaitingRoom()
    {
        return $this->escortUserFromWaitingRoom();
    }

    private function escortUserFromWaitingRoom()
    {
        return Auth::user()->guest ? redirect(route('auth.login', ['login_tab' => 2, 'guest_error' => 'removed_by_teacher'])) : redirect(route('student.dashboard'));
    }
}
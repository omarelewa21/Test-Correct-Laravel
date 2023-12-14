<?php

namespace tcCore\Http\Livewire\Student;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Ramsey\Uuid\Uuid;
use tcCore\Http\Helpers\AllowedAppType;
use tcCore\Http\Helpers\AppVersionDetector;
use tcCore\Http\Livewire\CoLearning\CompletionQuestion;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithStudentTestTakes;
use tcCore\TemporaryLogin;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\TestTakeStatus;

class WaitingRoom extends TCComponent
{
    use WithStudentTestTakes;

    protected function getListeners()
    {
        return [
            'start-test-take'                                                                                              => 'startTestTake',
            'start-discussing'                                                                                             => 'startDiscussing',
            'start-review'                                                                                                 => 'startReview',
            'start-graded'                                                                                                 => 'startReview',
            'is-test-take-open'                                                                                            => 'isTestTakeOpen',
            'echo-private:TestParticipant.' . $this->testParticipant->uuid . ',.TestTakeOpenForInteraction'                => 'isTestTakeOpen',
            'echo-private:TestParticipant.' . $this->testParticipant->uuid . ',.InbrowserTestingUpdatedForTestParticipant' => 'participantAppCheck',
            'echo-private:TestParticipant.' . $this->testParticipant->uuid . ',.RemoveParticipantFromWaitingRoom'          => 'removeParticipantFromWaitingRoom',
            'echo-private:TestParticipant.' . $this->testParticipant->uuid . ',.TestTakeForceTakenAway'                    => 'participantStatusChanged',
            'echo-private:TestParticipant.' . $this->testParticipant->uuid . ',.TestTakeReopened'                          => 'participantStatusChanged',
        ];
    }

    protected $queryString = [
        'take',
        'directly_to_review' => ['except' => false],
        'origin'             => ['except' => '']
    ];

    public $take;
    public $directly_to_review = false;
    public $origin = '';

    public $waitingTestTake;
    public $testParticipant;
    public $isTakeOpen;
    public $isTakeAlreadyTaken;
    public $countdownNumber = 3;
    public $testTakeStatusStage;
    public $participatingClasses = [];

    public $meetsAppRequirement = true;
    public $needsAppForTestTake;
    public $needsAppForCoLearning;
    public $appNeedsUpdate;
    public $appNeedsUpdateDeadline;
    public $appStatus;
    public $showGrades=true;
    public $previousURL;

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

        if ($this->directly_to_review) {
            $this->startReview();
        }

        $this->testTakeStatusStage = $this->waitingTestTake->determineTestTakeStage();
        $this->participatingClasses = $this->getParticipatingClasses($this->waitingTestTake);
        $this->participantAppCheck();

        $this->showGrades = $this->checkShowGrades();
        $this->previousURL =  url()->previous();
    }

    public function render()
    {
        $this->waitingTestTake = $this->getWaitingRoomTestTake();
        return view('livewire.student.waiting-room')->layout('layouts.student');
    }

    public function startTestTake()
    {
        // through the AppApi a Virtual Machine has been reported, so we cancel taking the test
        if ($this->testParticipant->test_take_status_id === TestTakeStatus::STATUS_TAKEN) {
            $this->testParticipant->test_take_status_id = TestTakeStatus::STATUS_TAKING_TEST;
            $this->testParticipant->save();
            $this->testParticipant->test_take_status_id = TestTakeStatus::STATUS_TAKEN;
            $this->testParticipant->save();
            return $this->escortUserFromWaitingRoom();
        }

        if ($this->waitingTestTake->test_take_status_id === TestTakeStatus::STATUS_TAKING_TEST) {
            if (!$this->testParticipant->isRejoiningTestTake(TestTakeStatus::STATUS_TAKING_TEST)) {
                $this->testParticipant->test_take_status_id = TestTakeStatus::STATUS_TAKING_TEST;
                $this->testParticipant->save();
            }
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
            if ($this->waitingTestTake->test->isAssignment()) {
                $this->isTakeOpen = $this->waitingTestTake->time_start <= now() && $this->waitingTestTake->time_end >= now();
                return;
            }

            $this->isTakeOpen = $testTakeStatus == TestTakeStatus::STATUS_TAKING_TEST;
        }

        if ($stage === 'discuss') {
            $this->isTakeOpen = $testTakeStatus == TestTakeStatus::STATUS_DISCUSSING && $this->waitingTestTake->fresh()->discussing_question_id !== null;
        }

        if ($stage === 'review') {
            $showResults = TestTake::whereUuid($this->take)->value('show_results');
            if ($this->canReviewTake($showResults)) {
                $this->isTakeOpen = $testTakeStatus == TestTakeStatus::STATUS_DISCUSSED;
            } else {
                $this->isTakeOpen = false;
            }
        }
        if ($stage === 'graded') {
            $showResults = TestTake::whereUuid($this->take)->value('show_results');
            if ($this->canOpenGradedTake($showResults)) {
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
            ->firstOrFail();
    }

    public function startDiscussing()
    {
        if (Auth::user()->schoolLocation->allow_new_co_learning) {
            return redirect('/student/co-learning/' . $this->take);
        }

        $url = 'test_takes/discuss/' . $this->take;
        $options = TemporaryLogin::buildValidOptionObject('page', $url);

        Auth::user()->redirectToCakeWithTemporaryLogin($options);
    }

    public function startReview()
    {
        if (Auth::user()->schoolLocation->allow_new_reviewing) {
            return redirect()->route('student.test-review', $this->take);
        }

        $url = 'test_takes/glance/' . $this->take;
        $url = filled($this->origin) ? $url . '?origin=' . $this->origin : $url;
        $options = TemporaryLogin::buildValidOptionObject('page', $url);

        return Auth::user()->redirectToCakeWithTemporaryLogin($options);
    }

    public function returnToGuestChoicePage()
    {
        session()->flush();
        $this->testParticipant->available_for_guests = true;
        $this->testParticipant->save();

        session()->put('guest_take', $this->take);
        session()->put('guest_data', [
            'name'        => $this->testParticipant->user->name,
            'name_first'  => $this->testParticipant->user->name_first,
            'name_suffix' => $this->testParticipant->user->name_suffix
        ]);
        return redirect(route('guest-choice', ['take' => $this->take]));
    }

    public function removeParticipantFromWaitingRoom()
    {
        return $this->escortUserFromWaitingRoom();
    }

    private function escortUserFromWaitingRoom()
    {
        $redirect = redirect(route('student.dashboard'));

        if (Auth::user()->guest) {
            $redirect = redirect(route('auth.login', [
                'login_tab'          => 2,
                'guest_message_type' => 'error',
                'guest_message'      => 'removed_by_teacher'
            ]));
        }

        return $redirect;
    }

    public function participantAppCheck()
    {
        if (!in_array($this->testTakeStatusStage, ['planned', 'taken', 'discuss' ]) ) {
            return $this->needsAppForTestTake = false;
        }

        $this->appStatus = AppVersionDetector::isVersionAllowed(session()->get('headers'));

        $this->needsAppForTestTake = !!(!$this->testParticipant->canUseBrowserTesting());
        $this->needsAppForCoLearning = !!(!$this->waitingTestTake->allow_inbrowser_colearning);
        $this->meetsAppRequirement = !!($this->appStatus != AllowedAppType::NOTALLOWED);
        $this->appNeedsUpdate = !!($this->appStatus === AllowedAppType::NEEDSUPDATE);

        if ($this->needsAppForTestTake && $this->meetsAppRequirement && !AppVersionDetector::verifyKeyHeader()) {
            // student is using a modified app since the tlckey header is incorrect
            $this->appStatus = AllowedAppType::NOTALLOWED;
        }

        if ($this->appNeedsUpdate) {
            $res = AppVersionDetector::needsUpdateDeadline();
            if ($res !== false) {
                $this->appNeedsUpdateDeadline = $res->isoFormat('LL');
            }
        }
    }

    public function participantStatusChanged()
    {
        $this->isTestTakeOpen();
    }

    public function getButtonTextForPlannedTakes()
    {
        if ($this->testParticipant->test_take_status_id >= TestTakeStatus::STATUS_HANDED_IN) {
            return __('student.test_already_taken');
        }
        return __('student.wait_for_test_take');
    }

    public function returnToTestTake()
    {
        redirect($this->previousURL);
    }

    /**
     * @param $showResults
     * @return bool
     */
    private function canReviewTake($showResults): bool
    {
        return $showResults != null && $showResults->gt(Carbon::now());// && $this->testParticipant->hasRating();
    }

    private function canOpenGradedTake($showResults): bool
    {
        return $showResults != null && $showResults->gt(Carbon::now()) && $this->testParticipant->hasRating();
    }

    private function checkShowGrades()
    {
        if($this->testTakeStatusStage == 'graded'){
            return $this->waitingTestTake->show_grades;
        }
        return false;
    }

    public function needsApp(): bool
    {
        if (!in_array($this->testTakeStatusStage, ['planned', 'taken', 'discuss'])) {
            return false;
        }

        return $this->testTakeStatusStage === 'planned' ? $this->needsAppForTestTake : $this->needsAppForCoLearning;
    }
}

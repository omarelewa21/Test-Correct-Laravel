<?php

namespace tcCore\Http\Livewire\Student;

use Carbon\Carbon;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Ramsey\Uuid\Uuid;
use tcCore\Http\Traits\WithStudentTestTakes;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\TestTakeStatus;

class WaitingRoom extends Component
{
    use WithStudentTestTakes;

    protected function getListeners()
    {
        return [
            'start-test-take'                                                                                   => 'startTestTake',
            'echo-private:TestParticipant.' . $this->testParticipant->getKey() . ',.TestTakeOpenForInteraction' => 'isTestTakeOpen',
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

    public function mount()
    {
        if (!isset($this->take) || !Uuid::isValid($this->take)) {
            return redirect(route('student.dashboard'));
        }
        $this->waitingTestTake = $this->getWaitingRoomTestTake();
        $this->testParticipant = TestParticipant::whereUserId(Auth::id())->whereTestTakeId($this->waitingTestTake->getKey())->first();
        $this->testTakeStatusStage = $this->determineTestTakeStatusStage();
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
            if ($showResults->gt(Carbon::now())) {
                $this->isTakeOpen = $testTakeStatus == TestTakeStatus::STATUS_DISCUSSED;
            } else {
                $this->isTakeOpen = false;
            }
        }
        if ($stage === 'graded') {

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

    private function determineTestTakeStatusStage(): string
    {
        $status = $this->waitingTestTake->test_take_status_id;

        $planned = [TestTakeStatus::STATUS_PLANNED, TestTakeStatus::STATUS_TEST_NOT_TAKEN, TestTakeStatus::STATUS_TAKING_TEST];
        $discuss = [TestTakeStatus::STATUS_TAKEN, TestTakeStatus::STATUS_DISCUSSING];
        $review = [TestTakeStatus::STATUS_DISCUSSED];
        $graded = [TestTakeStatus::STATUS_RATED];

        if (in_array($status, $planned)) return 'planned';
        if (in_array($status, $discuss)) return 'discuss';
        if (in_array($status, $review)) return 'review';
        if (in_array($status, $graded)) return 'graded';
    }
}
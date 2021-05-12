<?php

namespace tcCore\Http\Livewire\Student;

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

    protected $listeners = ['start-test-take' => 'startTestTake'];
    protected $queryString = ['take'];
    public $take;
    public $waitingTestTake;
    public $testParticipant;
    public $isTakeOpen;
    public $isTakeAlreadyTaken;
    public $countdownNumber = 3;

    public function mount()
    {
        if (!isset($this->take) || !Uuid::isValid($this->take)) {
            return redirect(route('student.dashboard'));
        }
        $this->waitingTestTake = $this->getWaitingRoomTestTake();
        $this->testParticipant = TestParticipant::whereUserId(Auth::id())->whereTestTakeId($this->waitingTestTake->getKey())->first();
    }

    public function render()
    {
        $this->waitingTestTake = $this->getWaitingRoomTestTake();
        return view('livewire.student.waiting-room')->layout('layouts.student');
    }

    public function startTestTake()
    {
        $this->redirectRoute('student.test-take-laravel', $this->take);
    }

    public function isTestTakeOpen()
    {
        $testParticipantStatus = TestParticipant::whereUserId(Auth::id())->whereTestTakeId($this->waitingTestTake->getKey())->value('test_take_status_id');
        $testTakeStatus = TestTake::whereUuid($this->take)->value('test_take_status_id');

        if ($testParticipantStatus > TestTakeStatus::STATUS_TAKING_TEST) {
            $this->isTakeOpen = false;
            $this->isTakeAlreadyTaken = true;
        }

        $this->isTakeOpen = $testTakeStatus == TestTakeStatus::STATUS_TAKING_TEST;
        $this->isTakeAlreadyTaken = $testTakeStatus > TestTakeStatus::STATUS_TAKING_TEST;
    }

    public function getCountdownNumber()
    {
        return $this->countdownNumber;
    }

    public function getWaitingRoomTestTake()
    {
        return TestTake::select('test_takes.*', 'subjects.name as subject_name', 'tests.name as test_name')
            ->join('tests', 'test_takes.test_id', '=', 'tests.id')
            ->join('subjects', 'tests.subject_id','=','subjects.id')
            ->where('test_takes.id', TestTake::whereUuid($this->take)->value('id'))
            ->first();
    }
}
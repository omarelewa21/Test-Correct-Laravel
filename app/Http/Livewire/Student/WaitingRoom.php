<?php

namespace tcCore\Http\Livewire\Student;

use Livewire\Component;
use Ramsey\Uuid\Uuid;
use tcCore\Http\Traits\WithStudentTestTakes;
use tcCore\TestTake;
use tcCore\TestTakeStatus;

class WaitingRoom extends Component
{
    use WithStudentTestTakes;

    protected $listeners = ['start-test-take' => 'startTestTake'];
    protected $queryString = ['take'];
    public $take;
    public $waitingTestTake;
    public $isTakeOpen;

    public function mount()
    {
        if (!isset($this->take) || !Uuid::isValid($this->take)) {
            return redirect(route('student.dashboard'));
        }

        $this->waitingTestTake = TestTake::whereUuid($this->take)->firstOrFail();

        if ($this->waitingTestTake->test_take_status_id > TestTakeStatus::STATUS_TAKING_TEST) {
            return redirect(route('student.dashboard'));
        }
        $this->isTakeOpen = $this->waitingTestTake->test_take_status_id == TestTakeStatus::STATUS_TAKING_TEST;
    }

    public function render()
    {
        return view('livewire.student.waiting-room')->layout('layouts.student');
    }

    public function startTestTake()
    {
        $this->redirectRoute('student.test-take-laravel', $this->take);
    }

    public function isTestTakeOpen()
    {
        $this->isTakeOpen = TestTake::whereUuid($this->take)->value('test_take_status_id') == TestTakeStatus::STATUS_TAKING_TEST;
        return $this->isTakeOpen;
    }
}
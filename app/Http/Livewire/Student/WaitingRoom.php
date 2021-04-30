<?php

namespace tcCore\Http\Livewire\Student;

use Livewire\Component;
use Ramsey\Uuid\Uuid;
use tcCore\Http\Traits\WithStudentTestTakes;
use tcCore\TestTakeStatus;

class WaitingRoom extends Component
{
    use WithStudentTestTakes;

    protected $queryString = ['take'];
    public $take;
    public $waitingTestTake;

    public function mount()
    {
        if (!isset($this->take) || !Uuid::isValid($this->take)) {
            return redirect(route('student.dashboard'));
        }

        $this->waitingTestTake = \tcCore\TestTake::whereUuid($this->take)->firstOrFail();

        if ($this->waitingTestTake->test_take_status_id > TestTakeStatus::STATUS_TAKING_TEST) {
            return redirect(route('student.dashboard'));
        }
    }

    public function render()
    {
        return view('livewire.student.waiting-room')->layout('layouts.student');
    }
}
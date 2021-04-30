<?php

namespace tcCore\Http\Livewire\Student;

use Livewire\Component;
use Ramsey\Uuid\Uuid;
use tcCore\Http\Traits\WithStudentTestTakes;

class WaitingRoom extends Component
{
    use WithStudentTestTakes;

    protected $queryString = [
        'waitingroom' => ['except' => false],
        'take'        => ['except' => ''],
    ];
    public $waitingroom;
    public $take;

    public $waitingTestTake;

    public function mount()
    {
        $this->performWaitingRoomCheck();
    }

    public function render()
    {
        return view('livewire.student.waiting-room');
    }

    private function getTestTakeDataForWaitingRoom($testTake)
    {
        $this->waitingTestTake = \tcCore\TestTake::whereUuid($testTake)->firstOrFail();
    }

    private function performWaitingRoomCheck()
    {
        if (!Uuid::isValid($this->take) || !$this->waitingroom) {
            $this->redirectRoute('student.test-takes');
        }
        $this->getTestTakeDataForWaitingRoom($this->take);
    }
}
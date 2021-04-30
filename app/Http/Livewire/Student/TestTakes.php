<?php

namespace tcCore\Http\Livewire\Student;

use Livewire\Component;
use Livewire\WithPagination;
use Ramsey\Uuid\Uuid;
use tcCore\Http\Traits\WithStudentTestTakes;

class TestTakes extends Component
{
    use WithPagination, WithStudentTestTakes;

    public $plannedTab = 1;
    public $discussTab = 2;
    public $reviewTab = 3;
    public $gradedTab = 4;
    public $waitingroomTab = 5;
    public $activeTab;

    protected $queryString = [
        'waitingroom' => ['except' => false],
        'take'        => ['except' => ''],
        'tab'         => ['except' => ''],
    ];
    public $waitingroom;
    public $take;
    public $tab;

    public $waitingTestTake;

    public function mount()
    {
        $this->activeTab = $this->plannedTab;

        $this->performWaitingRoomCheck();

        if ($this->tab) {
            $this->goToTab();
        }
    }

    public function render()
    {
        return view('livewire.student.test-takes', [
            'ratings'   => $this->getRatingsForStudent()
        ])->layout('layouts.student');
    }

    public function changeActiveTab($tab)
    {
        if ($tab != $this->waitingroomTab) {
            $this->waitingroom = false;
            $this->take = '';
        }

        $this->activeTab = $tab;
    }

    public function getTestTakeDataForWaitingRoom($testTake)
    {
        return \tcCore\TestTake::whereUuid($testTake)->firstOrFail();
    }

    private function performWaitingRoomCheck()
    {
        if (!$this->waitingroom) return;
        if (!Uuid::isValid($this->take)) {
            $this->take = '';
            $this->waitingroom = false;
            return;
        }
        $this->activeTab = $this->waitingroomTab;
        $this->waitingTestTake = $this->getTestTakeDataForWaitingRoom($this->take);
    }

    private function goToTab()
    {
        if($this->tab === 'planned') $this->changeActiveTab($this->plannedTab);
        if($this->tab === 'discuss') $this->changeActiveTab($this->discussTab);
        if($this->tab === 'review') $this->changeActiveTab($this->reviewTab);
        if($this->tab === 'graded') $this->changeActiveTab($this->gradedTab);
        $this->reset('tab');
    }
}

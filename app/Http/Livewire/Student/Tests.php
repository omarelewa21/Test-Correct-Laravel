<?php

namespace tcCore\Http\Livewire\Student;

use Livewire\Component;
use tcCore\Http\Traits\WithPersonalizedTestTakes;

class Tests extends Component
{
    use WithPersonalizedTestTakes;

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

        if ($this->waitingroom) {
            $this->activeTab = $this->waitingroomTab;
            $this->waitingTestTake = $this->getTestTakeDataForWaitingRoom($this->take);
        }
        if ($this->tab === 'grades') {
            $this->tab = '';
            $this->activeTab = $this->gradedTab;
        }
    }

    public function render()
    {
        return view('livewire.student.tests', [
            'testTakes' => $this->fetchTestTakes(),
            'ratings'   => $this->getRatings()
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
}

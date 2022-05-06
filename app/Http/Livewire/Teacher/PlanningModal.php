<?php

namespace tcCore\Http\Livewire\Teacher;

use Livewire\Component;
use tcCore\Test;
use tcCore\Period;
use tcCore\TestTake;

class PlanningModal extends Component
{
    protected $listeners = ['displayModal'];

    public $test;

    public $showModal = false;

    public $allowedPeriods;

    public $testTake;

    public function mount()
    {
        $this->test = new Test();
        $this->allowedPeriods = Period::filtered(['current_school_year' => true])->get();

        $this->testTake = new TestTake();
//        $this->testTake->
    }

    public function displayModal($testUuid)
    {
        $this->test = \tcCore\Test::whereUuid($testUuid)->first();

        $this->showModal = true;
    }

    public function render()
    {
        return view('livewire.teacher.planning-modal');
    }
}

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


    }

    protected $rules = [
        'testTake.*' => 'sometimes',
    ];

    public function displayModal($testUuid)
    {
        $this->test = \tcCore\Test::whereUuid($testUuid)->first();

        $this->testTake = new TestTake();

        $this->testTake->visible = 1;
        $this->testTake->date = now();
        $this->testTake->period_id = $this->allowedPeriods->first()->getKey();
        $this->testTake->invigilators = [auth()->id()];

//        $this->testTake->class_id = 1;

        $this->testTake->weight = 1;
        $this->testTake->test_id = $this->test->getKey();
        $this->testTake->allow_inbrowser_testing = 0;
        $this->testTake->invigilator_note = '';
        $this->testTake->test_kind_id = 3;
        $this->testTake->test_take_status_id = 3;
        $this->testTake->retake = 0;

//        $this->testTake->school_classes = [1];

        $this->testTake->user_id =  auth()->user()->id;

        $this->showModal = true;
    }

    public function plan(){
        $this->testTake->date = now()->format('Y-m-d');
//        $this->testTake->invigilatorUsers()->save(auth()->user());
        $arr = ($this->testTake->toArray());
        unset($arr['invigilator_users']);
        unset($arr['exported_to_rtti_formated']);
        unset($arr['invigilators_acceptable']);
        unset($arr['invigilators_unacceptable_message']);

//        dd($arr);

        $this->testTake->fill($arr);
        $this->testTake->save();
    }

    public function render()
    {
        return view('livewire.teacher.planning-modal');
    }
}

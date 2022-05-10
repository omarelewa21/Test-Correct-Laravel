<?php

namespace tcCore\Http\Livewire\Teacher;

use Livewire\Component;
use tcCore\Test;
use tcCore\Period;
use tcCore\TestTake;
use tcCore\TestTakeStatus;

class PlanningModal extends Component
{
    protected $listeners = ['displayModal'];

    public $test;

    public $showModal = false;

    public $allowedPeriods;

    public $request= [];

    public function isAssessmentType() {
        return $this->test->isAssignment();
    }

    public function mount()
    {
        $this->test = new Test();
        $this->allowedPeriods = Period::filtered(['current_school_year' => true])->get();
    }

    protected $rules = [
        'request.*' => 'sometimes',
    ];

    public function displayModal($testUuid)
    {
        $this->test = \tcCore\Test::whereUuid($testUuid)->first();

        $this->resetModalRequest();

        $this->showModal = true;
    }

    public function plan(){

        $t = new TestTake();
        $this->request['time_start'] = $this->request['date'];
        $this->request['test_take_status_id'] = TestTakeStatus::STATUS_PLANNED;

        $t->fill($this->request);
        $t->setAttribute('user_id', auth()->id());
        $t->save();
    }
    
    private function resetModalRequest(){
        $this->request= [];

        $this->request['visible'] = 1;
        $this->request['date'] = now()->format('d-m-Y');
        if ($this->isAssessmentType()) {
            $this->request['date_till'] = now();
        }
        $this->request['period_id'] = $this->allowedPeriods->first()->getKey();
        $this->request['invigilators'] = [auth()->id()];
        $this->request['weight'] = 5;
        $this->request['test_id'] = $this->test->getKey();
        $this->request['allow_inbrowser_testing'] = 0;
        $this->request['invigilator_note'] = '';
        $this->request['test_kind_id'] = 3;

        $this->request['retake'] = 0;
        $this->request['guest_accounts'] = 0;
    }

    public function render()
    {
        return view('livewire.teacher.planning-modal');
    }
}

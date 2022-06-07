<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Http\Request;
use LivewireUI\Modal\ModalComponent;
use tcCore\Http\Controllers\InvigilatorsController;
use tcCore\Http\Controllers\TemporaryLoginController;
use tcCore\SchoolClass;
use tcCore\Test;
use tcCore\Period;
use tcCore\TestTake;
use tcCore\TestTakeStatus;

class PlanningModal extends ModalComponent
{
    protected $listeners = ['showModal'];

    public $test;

    public $allowedPeriods;

    public $allowedInvigilators = [];

    public $request = ['date' => '', 'schoolClasses' => [], 'invigilators' => []];

    public $schoolClasses = [];


    public $selectedClassesContainerId;
    public $selectedInvigilatorsContrainerId;

    public function isAssessmentType()
    {
        return $this->test->isAssignment();
    }

    public function mount($testUuid)
    {
        $this->test = \tcCore\Test::whereUuid($testUuid)->first();

        $this->allowedPeriods = Period::filtered(['current_school_year' => true])->get();
        $this->schoolClasses = SchoolClass::filtered(['user_id' => auth()->id()], [])
            ->get(['id', 'name'])
            ->map(function ($item) {
                return ['value' => (int)$item->id, 'label' => $item->name];
            })->toArray();
        $this->resetModalRequest();

        $this->allowedInvigilators = InvigilatorsController::getInvigilatorList()->map(function ($invigilator) {
            return [
                'value' => $invigilator->id,
                'label' => collect([$invigilator->name_first, $invigilator->name])->join(' ')
            ];
        })->values()->toArray();

        $this->resetModalRequest();
    }

    protected $rules = [
        'request.*' => 'sometimes',
    ];


    public function plan()
    {
        $this->planTest();

        $controller = new TemporaryLoginController();
        $request = new Request();

        $request->merge([
            'options' => [
                'page'        => '/',
                'page_action' => "Navigation.load('/test_takes/planned_teacher')"
            ]
        ]);

        redirect($controller->toCakeUrl($request));

    }

    private function planTest()
    {
        $t = new TestTake();
        $this->request['time_start'] = $this->request['date'];
        $this->request['test_take_status_id'] = TestTakeStatus::STATUS_PLANNED;

        $t->fill($this->request);

        $t->setAttribute('user_id', auth()->id());
        $t->save();

        $this->dispatchBrowserEvent('notify', ['message' => __('teacher.testtake planned')]);
    }

    public function planNext()
    {
        $this->planTest();

        $this->closeModal();
    }

    private function resetModalRequest()
    {
        $this->selectedClassesContainerId = 'selected_classes'.$this->test->getKey();
        $this->selectedInvigilatorsContrainerId = 'selected_invigilator'.$this->test->getKey();

        $this->request = [];

        $this->request['visible'] = 1;
        $this->request['date'] = now()->format('Y-m-d');
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
        $this->request['schoolClasses'] = [];
        $this->request['invigilators'] = [auth()->id()];
    }

    public function render()
    {
        return view('livewire.teacher.planning-modal');
    }
}

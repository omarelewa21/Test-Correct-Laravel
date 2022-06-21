<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Component;
use LivewireUI\Modal\ModalComponent;
use tcCore\EducationLevel;
use tcCore\Http\Controllers\TemporaryLoginController;
use tcCore\Period;
use tcCore\Subject;
use tcCore\Test;
use tcCore\TestKind;

class TestCreateModal extends ModalComponent
{
    public bool $forceClose = true;

    public $allowedTestKinds = [];

    public $allowedSubjects = [];

    public $allowedPeriods = [];

    public $allowedEductionLevels = [];


    public $request = [];

    protected function rules()
    {
        $allowedTestKindIds = 'in:' . collect($this->allowedTestKinds)->map(function ($testKind) {
                return property_exists($testKind, 'id') ? $testKind->id : $testKind['id'];
            })->join(',');

        $allowedSubjectIds = 'in:' . collect($this->allowedSubjects)->map(function ($subject) {
                return property_exists($subject, 'id') ? $subject->id : $subject['id'];
            })->join(',');

        $allowedEducationLevelIds = 'in:' . collect($this->allowedEductionLevels)->map(function ($educationLevel) {
                return property_exists($educationLevel, 'id') ? $educationLevel->id : $educationLevel['id'];
            })->join(',');

        $allowedPeriodIds = 'in:' . collect($this->allowedPeriods)->map(function ($period) {
                return property_exists($period, 'id') ? $period->id : $period['id'];
            })->join(',');


        return [
            'request.name'                 => 'required|min:3|unique:tests,name,NULL,id,author_id,' . Auth::id() . ',deleted_at,NULL,is_system_test,0',
            'request.abbreviation'         => 'required|max:5',
            'request.test_kind_id'         => ['required', 'integer', $allowedTestKindIds],
            'request.subject_id'           => ['required', 'integer', $allowedSubjectIds],
            'request.education_level_id'   => ['required', 'integer', $allowedEducationLevelIds],
            'request.education_level_year' => 'required|integer|between:1,6',
            'request.period_id'            => ['required', 'integer', $allowedPeriodIds],
            'request.shuffle'              => 'required|boolean',
            'request.introduction'         => 'sometimes',
        ];
    }

    protected function getMessages()
    {
        return [
            'request.name.unique' => __('validation.unique', ['attribute' => __('validation.test name')]),
        ];
    }

    public function mount()
    {
        $this->allowedSubjects = Subject::filtered(['user_current' => auth()->id()], ['name' => 'asc'])->get(['id', 'name'])->keyBy('id');
        $this->allowedTestKinds = TestKind::orderBy('name', 'asc')->get(['name', 'id']);
        $this->allowedPeriods = Period::filtered(['current_school_year' => 1], [])->get(['id', 'name', 'start_date', 'end_date'])->keyBy('id');
        $this->allowedEductionLevels = EducationLevel::filtered(['user_id' => auth()->id()], [])->select(['id', 'name', 'max_years', 'uuid'])->get()->keyBy('id');

        $this->request = [
            'name'                 => '',
            'abbreviation'         => '',
            'test_kind_id'         => 3,
            'subject_id'           => $this->allowedSubjects->first()->id,
            'education_level_id'   => $this->allowedEductionLevels->first()->id,
            'education_level_year' => 1,
            'period_id'            => $this->allowedPeriods->first()->id,
            'shuffle'              => 0,
            'introduction'         => '',
        ];
    }

    public function getMaxEducationLevelYearProperty()
    {
        if ($this->request['education_level_id']) {
            $returnValue =  $this->allowedEductionLevels->where('id', $this->request['education_level_id'])->first()->max_years;
        }

        return $returnValue ?: 6;
    }

    public function submit()
    {
        $this->validate();
        $test = new Test($this->request);
        $test->setAttribute('author_id', Auth::id());
        $test->setAttribute('owner_id', Auth::user()->school_location_id);
        $test->save();
        $this->showModal = false;

        redirect(
            route('teacher.question-editor',
                [
                    'action'         => 'add',
                    'owner'          => 'test',
                    'testId'         => $test->uuid,
                    'testQuestionId' => '',
                    'type'           => '',
                    'isCloneRequest' => '',
                    'withDrawer'     => 'true',
                    'referrer'       => 'teacher.tests',
                ]
            )
        );

        $this->dispatchBrowserEvent('notify', ['message' => __('teacher.test created')]);
    }

    public function render()
    {
        return view('livewire.teacher.test-create-modal');
    }
}

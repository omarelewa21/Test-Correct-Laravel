<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use tcCore\EducationLevel;
use tcCore\Http\Controllers\TemporaryLoginController;
use tcCore\Period;
use tcCore\Test;
use tcCore\TestKind;

class TestCreateModal extends Component
{
    public $showModal = true;
    public $modalId = 'test-create-modal';

    public $allowedTestKinds = [];

    public $allowedSubjects = [];

    public $allowedPeriods = [];

    public $allowedEductionLevels = [];

    protected $listeners = [
        'showModal'
    ];

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
            'request.name'                 => 'required|min:3',
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

    public function mount()
    {
        $this->allowedSubjects = EducationLevel::filtered(['user_id' => auth()->id()], [])->select(['id', 'name', 'max_years', 'uuid'])->get()->keyBy('id');
        $this->allowedTestKinds = TestKind::orderBy('name', 'asc')->get([ 'name', 'id' ]);
        $this->allowedPeriods = Period::filtered(['current_school_year' => 1], [])->get(['id', 'name', 'start_date', 'end_date'])->keyBy('id');
        $this->allowedEductionLevels = EducationLevel::filtered(['user_id' => auth()->id()], [])->select(['id', 'name', 'max_years', 'uuid'])->get()->keyBy('id');

        $this->request = [
            'name'                 => 'titel',
            'abbreviation'         => 'af',
            'test_kind_id'         => '1',
            'subject_id'           => '16',
            'education_level_id'   => 1,
            'education_level_year' => 1,
            'period_id'            => 1,
            'shuffle'              => 0,
            'introduction'         => 'Intor text',
        ];
    }

    public function showModal()
    {
        $this->showModal = !$this->showModal;
    }

    public function hideModal()
    {
        $this->showModal = false;
    }

    public function submit()
    {
        $this->validate();
        $test = new Test($this->request);
        $test->setAttribute('author_id', Auth::id());
        $test->setAttribute('owner_id', Auth::user()->school_location_id);
        $test->save();
        $this->showModal = false;

        $this->dispatchBrowserEvent('notify', ['message' => __('teacher.test created')]);
    }

    public function goToUploadTest()
    {
        $this->showModal = false;
        $controller = new TemporaryLoginController();
        $request = new Request();
        $request->merge([
            'options' => [
                'page'        => '/',
                'page_action' => "Loading.show();Popup.load('/file_management/upload_test',800);"
            ],
        ]);

        redirect($controller->toCakeUrl($request));
    }

    public function render()
    {
        return view('livewire.teacher.test-create-modal');
    }
}

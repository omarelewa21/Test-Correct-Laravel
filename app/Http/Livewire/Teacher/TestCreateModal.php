<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Http\Request;
use Livewire\Component;
use tcCore\EducationLevel;
use tcCore\Http\Controllers\TemporaryLoginController;
use tcCore\Period;
use tcCore\TestKind;

class TestCreateModal extends Component
{
    public $showModal = false;
    public $modalId = 'test-create-modal';

    public $allowedTypes= [];

    public $allowedSubjects=[];

    public $allowedPeriods=[];

    public $allowedEductionLevels = [];

    protected $listeners = [
        'showModal'
    ];

    public $request = [];

    protected $rules = [
        'request.name'                 => 'required|min:3',
        'request.abbreviation'         => 'required|max:5',
        'request.test_kind_id'         => 'required|integer',
        'request.subject_id'           => 'required|integer',
        'request.education_level_id'   => 'required|integer',
        'request.education_level_year' => 'required|integer|between:1,6',
        'request.period_id'            => 'required|integer',
        'request.shuffle'              => 'required|boolean',
        'request.introduction'         => 'sometimes',
    ];

    public function mount()
    {
        $this->allowedSubjects = EducationLevel::filtered(['user_id'=> auth()->id()], [])->select(['id', 'name', 'max_years', 'uuid'])->get()->keyBy('id');
        $this->allowedTypes = TestKind::orderBy('name', 'asc')->get('name', 'id');


        $this->allowedPeriods = Period::filtered( ['current_school_year' => 1],  [])->get(['id', 'name', 'start_date', 'end_date'])->keyBy('id');
        $this->allowedEductionLevels = 	EducationLevel::filtered(['user_id'=> auth()->id()],  [])->select(['id', 'name', 'max_years', 'uuid'])->get()->keyBy('id');


        $this->request = [
            'name'                 => 'titel',
            'abbreviation'         => 'af',
            'test_kind_id'         => '1',
            'subject_id'           => '16',
            'education_level_id'   => '1',
            'education_level_year' => '1',
            'period_id'            => '1',
            'shuffle'              => '0',
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
        dd($this);


        //$this->showModal = false;

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

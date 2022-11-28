<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Support\Facades\Auth;
use LivewireUI\Modal\ModalComponent;
use tcCore\Http\Traits\Modal\TestActions;
use tcCore\Period;
use tcCore\Test;

class TestCreateModal extends ModalComponent
{
    use TestActions;

    public bool $forceClose = true;

    public $request = [];

    public function mount()
    {
        if (Auth::user()->isValidExamCoordinator()) {
            abort(403);
        }
        $this->allowedSubjects = $this->getAllowedSubjects();
        $this->allowedTestKinds = $this->getAllowedTestKinds();
        $this->allowedPeriods = $this->getAllowedPeriods();
        $this->allowedEductionLevels = $this->getAllowedEducationLevels();

        $this->request = [
            'name'                 => '',
            'abbreviation'         => '',
            'test_kind_id'         => 3,
            'subject_id'           => $this->allowedSubjects->first()?->id,
            'education_level_id'   => $this->allowedEductionLevels->first()?->id,
            'education_level_year' => 1,
            'period_id'            => Period::filtered(['current_school_year' => true])->first()->id ?? $this->allowedPeriods->first()->id,
            'shuffle'              => 0,
            'introduction'         => '',
        ];
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

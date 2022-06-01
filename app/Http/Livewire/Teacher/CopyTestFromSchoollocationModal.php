<?php

namespace tcCore\Http\Livewire\Teacher;

use Livewire\Component;
use tcCore\Subject;
use tcCore\Test;

class CopyTestFromSchoollocationModal extends Component
{
    public $showModal = false;

    public $test;

    protected $listeners = ['showModal'];

    public $base_subject = '';

    public $allowedSubjectsForExamnSubjects = [];

    protected function rules()
    {
        return [
            'request.name'       => 'required',
            'request.subject_id' => 'ruquired',
        ];
    }

    public function mount()
    {
        $this->test = new Test;

    }

    public function showModal($testUuid)
    {
        $this->test = \tcCore\Test::whereUuid($testUuid)->first();

        $this->base_subject = $this->test->subject->baseSubject->name;

        $this->allowedSubjectsForExamnSubjects = Subject::allowedSubjectsByBaseSubjectForUser($this->test->subject->baseSubject,
            auth()->user())->pluck('name', 'id');
        $this->test->subject_id = $this->allowedSubjectsForExamnSubjects->keys()->first();

        $this->showModal = true;
    }

    public function duplicateTest($testUuid)
    {
        // @TODO only duplicate when allowed?
        $this->validate();

        $test = Test::whereUuid($testUuid)->first();
        if ($test == null) {
            return 'Error no test was found';
        }

        if (!$test->canDuplicate()) {
            return 'Error duplication not allowed';
        }

        try {
            $newTest = $test->userDuplicate(
                [
                    'school_location_id' => Auth::user()->school_location_id,
                    'subject_id'         => $this->request['subject_id'],
                    'name'               => $this->request['name'],
                ], Auth::id()
            );
        } catch (\Exception $e) {
            return 'Error duplication failed';
        }

        return __('general.duplication successful');


    }


    public function render()
    {
        return view('livewire.teacher.copy-test-from-schoollocation-modal');
    }
}

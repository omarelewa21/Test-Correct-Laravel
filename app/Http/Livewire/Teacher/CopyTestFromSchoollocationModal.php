<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use tcCore\Subject;
use tcCore\Test;

class CopyTestFromSchoollocationModal extends Component
{
    public $showModal = false;

    private $test;

    public $testUuid;

    protected $listeners = ['showModal'];

    public $base_subject = '';

    public $allowedSubjectsForExamnSubjects = [];

    public $request = [];

    protected function rules()
    {
        $rules = [
            'request.name'       => 'required|string|min:4',
            'request.subject_id' => [
                'required',
                'int',
            ]
        ];

        if ($this->test) {
            $rules['request.subject_id'][] = Rule::in(
                Subject::allowedSubjectsByBaseSubjectForUser($this->test->subject->baseSubject, auth()->user())
                    ->pluck('id')
            );
        }

        return $rules;
    }

    public function showModal($testUuid)
    {
        $this->test = Test::whereUuid($testUuid)->first();
        if ($this->test) { // ??
            $this->testUuid = $this->test->uuid;
        }
        $this->request['name'] = $this->test->name;

        $this->base_subject = $this->test->subject->baseSubject->name;
        $this->allowedSubjectsForExamnSubjects = Subject::allowedSubjectsByBaseSubjectForUser(
            $this->test->subject->baseSubject,
            auth()->user()
        )->pluck('name', 'id');
        $this->request['subject_id'] = $this->allowedSubjectsForExamnSubjects->keys()->first();

        $this->showModal = true;
    }

    public function copy($testUuid)
    {
        // @TODO only duplicate when allowed?
        $this->validate();

        $test = Test::whereUuid($testUuid)->first();
        if ($test == null) {
            return 'Error no test was found';
        }

        if (! $test->canCopyFromSchool(auth()->user())) {
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

        $this->dispatchBrowserEvent('notify', ['message' => __('general.duplication successful')]);
        $this->showModal = false;
        $this->emitTo('teacher.tests-overview', 'test-added');
    }


    public function render()
    {
        return view('livewire.teacher.copy-test-from-schoollocation-modal');
    }
}

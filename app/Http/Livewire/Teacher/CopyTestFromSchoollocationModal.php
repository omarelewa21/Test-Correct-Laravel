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

    protected function rules(){
        return ['test.name' => 'required'];
    }

    public function mount(){
        $this->test = new Test;

    }

    public function showModal($testUuid)
    {
        $this->test = \tcCore\Test::whereUuid($testUuid)->first();

        $this->base_subject = $this->test->subject->baseSubject->name;

        $this->allowedSubjectsForExamnSubjects = Subject::allowedSubjectsByBaseSubjectForUser($this->test->subject->baseSubject, auth()->user())->pluck('name', 'id');
        $this->test->subject_id = $this->allowedSubjectsForExamnSubjects->keys()->first();

        $this->showModal = true;
    }


    public function render()
    {
        return view('livewire.teacher.copy-test-from-schoollocation-modal');
    }
}

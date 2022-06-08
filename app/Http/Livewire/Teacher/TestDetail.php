<?php

namespace tcCore\Http\Livewire\Teacher;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use tcCore\EducationLevel;
use tcCore\Http\Controllers\AuthorsController;
use tcCore\Http\Controllers\SubjectsController;
use tcCore\Http\Controllers\TemporaryLoginController;
use tcCore\Http\Requests\DuplicateTestRequest;
use tcCore\Subject;
use tcCore\Test;

class TestDetail extends Component
{


    public $uuid;


    public function mount($uuid)
    {
        $this->uuid = $uuid;

    }


    public function render()
    {
        $test = Test::whereUuid($this->uuid)
            ->with([
                'testQuestions' => function ($query) {
                    $query->orderBy('test_questions.order', 'asc');
                },
                'testQuestions.question'
            ])
            ->first();



        return view('livewire.teacher.test-detail')->layout('layouts.app-teacher')->with(compact(['test']));
    }

}

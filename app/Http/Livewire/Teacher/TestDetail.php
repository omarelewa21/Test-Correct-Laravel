<?php

namespace tcCore\Http\Livewire\Teacher;

use Livewire\Component;
use tcCore\Test;

class TestDetail extends Component
{
    public $uuid;
    protected $test;

    public function mount($uuid)
    {
        $this->uuid = $uuid;
    }

    public function booted()
    {
        $this->test = Test::whereUuid($this->uuid)->first();
    }

    public function getAmountOfQuestionsProperty()
    {
        return $this->test->getAmountOfQuestions();
    }

    public function render()
    {
        $test = Test::whereUuid($this->uuid)
            ->with([
                'testQuestions' => function ($query) {
                    $query->orderBy('test_questions.order', 'asc');
                },
                'testQuestions.question',
                'testQuestions.question.authors'
            ])
            ->first();

        return view('livewire.teacher.test-detail')->layout('layouts.app-teacher')->with(compact(['test']));
    }

    public function redirectToTestOverview()
    {
        redirect()->to(route('teacher.tests'));
    }

}

<?php

namespace tcCore\Http\Livewire\Drawer;

use Livewire\Component;
use tcCore\Test;

class Cms extends Component
{
    protected $queryString = ['testId', 'testQuestionId', 'action'];

    /* Querystring parameters*/
    public string $testId = '';
    public string $testQuestionId = '';
    public string $action = '';

    public $testQuestionUuids = [];

    public function mount()
    {
        $this->testQuestionUuids = Test::whereUuid($this->testId)->first()->testQuestions()->pluck('uuid');
    }

    public function render()
    {
        return view('livewire.drawer.cms');
    }

    public function showQuestion($questionUuid)
    {
        $this->emitTo('teacher.questions.open-short', 'showQuestion', $questionUuid);

        $this->testQuestionId = $questionUuid;
    }
}
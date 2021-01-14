<?php

namespace tcCore\Http\Livewire\Student;

use Illuminate\Support\Str;
use Livewire\Component;
use tcCore\TestTake as Test;


class TestTakeStub extends Component
{
    public $testTake;
    public $question = 1;
    protected $queryString = ['question'];
    public $content;
    public $mainQuestion;
    public $component;
    public $showModal = false;

    public function mount(Test $test_take)
    {
        $this->testTake = $test_take;
        $this->testTake->load(['test', 'test.testQuestions', 'test.testQuestions.question'])->toArray();


        $this->setMainQuestion($this->question);
    }

    public function render()
    {
        return view('livewire.student.test-take_stub')->layout('layouts.app');
    }

    public function setMainQuestion(int $question)
    {

        $this->question = $question;
        $this->mainQuestion = $this->testTake->test->testQuestions->first(function ($item, $index) use ($question) {
            return $index === $question;
        });


        $this->component = 'question.' . Str::kebab($this->mainQuestion->type);
    }

    public function modal()
    {
        $this->showModal = true;
    }
}

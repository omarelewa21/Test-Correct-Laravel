<?php

namespace tcCore\Http\Livewire\Student;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Livewire\Component;
use tcCore\Answer;
use tcCore\Lib\Question\QuestionInterface;
use tcCore\MultipleChoiceQuestion;
use tcCore\MultipleChoiceQuestionAnswer;
use tcCore\TestParticipant;
use tcCore\TestTake as Test;


class TestTake extends Component
{
    public $testTake;
    public $question = 1;
    protected $queryString = ['question'];
    public $content;
    public $mainQuestion;
    public $component;

    public function mount(Test $test_take)
    {
        $this->testTake = $test_take;
        $this->testTake->load(['test', 'test.testQuestions', 'test.testQuestions.question'])->toArray();

        logger('mount');

        $this->setMainQuestion($this->question);
    }

    public function render()
    {
        return view('livewire.student.test-take')->layout('layouts.app');
    }

    public function setMainQuestion(int $question)
    {

        $this->question = $question;
        $this->mainQuestion = $this->testTake->test->testQuestions->first(function($item, $index) use ($question){
           return $index === $question;
        });


        $this->component = 'question.'. Str::kebab($this->mainQuestion->type);
    }
}

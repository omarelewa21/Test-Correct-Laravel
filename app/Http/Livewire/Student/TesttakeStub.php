<?php

namespace tcCore\Http\Livewire\Student;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Question;
use tcCore\TestTake as Test;


class TesttakeStub extends TCComponent
{

    public $testQuestions;
    public $question;
    protected $queryString = ['question'];
    public $content;
    public $mainQuestion;
    public $component;
    public $number = 1;

    public function mount(Test $test_take)
    {
        $this->testQuestions = self::getData($test_take);
        session()->put('data', serialize($this->testQuestions));
        $this->setMainQuestion($this->testQuestions->first()->uuid);
    }

    public function hydrate()
    {
        $this->testQuestions = unserialize(session()->get('data'));
    }

    public function previousQuestion()
    {
        $this->question = $this->testQuestions->get($this->number - 2)->uuid;
        $this->setMainQuestion($this->question);
    }

    public function nextQuestion()
    {
        $this->question = $this->testQuestions->get($this->number)->uuid;
        $this->setMainQuestion($this->question);
    }

    public function render()
    {
        return view('livewire.student.test-take_stub')->layout('layouts.app');
    }

    public function setMainQuestion($questionUuid)
    {
        $this->question = $questionUuid;
        $this->mainQuestion = Question::whereUuid($questionUuid)->first();
        $key = $this->testQuestions->search(function ($value, $key) use ($questionUuid) {
            return $value->uuid === $questionUuid;
        });
        $this->number = $key+1;

        $this->emit('questionUpdated');
    }

    public static function getData(Test $testTake)
    {
        $visibleAttributes = ['id', 'uuid', 'score', 'type', 'question', 'styling'];
        $testTake->load(['test', 'test.testQuestions', 'test.testQuestions.question'])->get();

        return $testTake->test->testQuestions->flatMap(function ($testQuestion) use ($visibleAttributes) {
            if ($testQuestion->question->type === 'GroupQuestion') {
                return $testQuestion->question->groupQuestionQuestions->map(function ($item) use ($visibleAttributes) {
                    $hideAttributes = array_keys($item->question->getAttributes());

                    $item->question->makeHidden($hideAttributes)->makeVisible($visibleAttributes);

                    return $item->question;
                });
            }
            $hideAttributes = array_keys($testQuestion->question->getAttributes());
            $testQuestion->question->makeHidden($hideAttributes)->makeVisible($visibleAttributes);

            return collect([$testQuestion->question]);
        });
    }
}

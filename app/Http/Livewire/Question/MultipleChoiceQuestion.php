<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;
use tcCore\Question;

class MultipleChoiceQuestion extends Component
{
    public $uuid;

    private $question;

    public $answer = '';

    public $arqStructure = [
        ['test_take.correct', 'test_take.correct', 'test_take.correct_reason'],
        ['test_take.correct', 'test_take.correct', 'test_take.incorrect_reason'],
        ['test_take.correct', 'test_take.incorrect', 'test_take.not_applicable'],
        ['test_take.incorrect', 'test_take.correct', 'test_take.not_applicable'],
        ['test_take.incorrect', 'test_take.incorrect', 'test_take.not_applicable'],
    ];

    protected $listeners = ['questionUpdated' => 'questionUpdated'];

    public function questionUpdated($uuid, $answer)
    {
        $this->uuid = $uuid;
        $this->answer = $answer;
    }

    public function updatedAnswer($value)
    {
        $this->emitUp('updateAnswer', $this->uuid, $value);
    }

    public function render()
    {
        $question = Question::whereUuid($this->uuid)->first();
        if ($question->subtype=='ARQ') {
            return view('livewire.question.arq-question', compact('question'));
        }

        return view('livewire.question.multiple-choice-question', compact('question'));
    }
}

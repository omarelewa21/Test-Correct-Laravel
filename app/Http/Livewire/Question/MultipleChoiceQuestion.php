<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;
use tcCore\Question;

class MultipleChoiceQuestion extends Component
{
    public $uuid;

    private $question;

    public $answer = '';

    public $answerStruct;

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
        $selectedAnswer = collect($this->answer)->filter(function($item) {
            return $item == 1;
        })->toArray();
        $this->answer = key($selectedAnswer);
    }

    public function mount()
    {
        $this->answerStruct =
            array_fill_keys(
                array_keys(
                    array_flip(Question::whereUuid($this->uuid)
                        ->first()
                        ->multipleChoiceQuestionAnswers->pluck('id')
                        ->toArray()
                    )
                ), 0
            );
    }

    public function updatedAnswer($value)
    {
        $this->answerStruct = array_fill_keys(array_keys($this->answerStruct),0);
        $this->answerStruct[$value] = 1;

        $this->emitUp('updateAnswer', $this->uuid, $this->answerStruct);
    }

    public function render()
    {
        $this->question = Question::whereUuid($this->uuid)->first();
        if ($this->question->subtype == 'ARQ') {
            return view('livewire.question.arq-question', ['question' => $this->question]);
        }

        return view('livewire.question.multiple-choice-question', ['question' => $this->question]);
    }
}

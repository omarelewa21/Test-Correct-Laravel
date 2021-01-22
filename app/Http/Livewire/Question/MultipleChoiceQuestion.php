<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;
use tcCore\Question;

class MultipleChoiceQuestion extends Component
{

    public $question;

    public $answer = '';

    public $answerStruct;
    public $number;

    public $arqStructure = [
        ['A', 'test_take.correct', 'test_take.correct', 'test_take.correct_reason'],
        ['B', 'test_take.correct', 'test_take.correct', 'test_take.incorrect_reason'],
        ['C', 'test_take.correct', 'test_take.incorrect', 'test_take.not_applicable'],
        ['D', 'test_take.incorrect', 'test_take.correct', 'test_take.not_applicable'],
        ['E', 'test_take.incorrect', 'test_take.incorrect', 'test_take.not_applicable'],
    ];

    protected $listeners = ['questionUpdated' => 'questionUpdated'];

    public function questionUpdated($uuid, $answer)
    {

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
                    array_flip(Question::whereUuid($this->question->uuid)
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
        dd($this->answerStruct);

//        $this->emitUp('updateAnswer', $this->uuid, $this->answerStruct);
    }

    public function render()
    {
        if ($this->question->subtype == 'ARQ') {
            return view('livewire.question.arq-question', ['question' => $this->question]);
        }

        return view('livewire.question.multiple-choice-question', ['question' => $this->question]);
    }
}

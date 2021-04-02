<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Requests\Request;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithQuestionTimer;
use tcCore\Question;

class MultipleChoiceQuestion extends Component
{
    use WithAttachments, WithNotepad, withCloseable, WithGroups;

    public $question;

    public $answer = '';

    public $answers;

    public $answerStruct;
    public $shuffledKeys;

    public $number;

    public $arqStructure = [
        ['A', 'test_take.correct', 'test_take.correct', 'test_take.correct_reason'],
        ['B', 'test_take.correct', 'test_take.correct', 'test_take.incorrect_reason'],
        ['C', 'test_take.correct', 'test_take.incorrect', 'test_take.not_applicable'],
        ['D', 'test_take.incorrect', 'test_take.correct', 'test_take.not_applicable'],
        ['E', 'test_take.incorrect', 'test_take.incorrect', 'test_take.not_applicable'],
    ];

    protected $listeners = ['questionUpdated' => 'questionUpdated'];

    public $answerText;


    public function mount()
    {

        if (!empty(json_decode($this->answers[$this->question->uuid]['answer']))) {
            $this->answerStruct = json_decode($this->answers[$this->question->uuid]['answer'], true);
        } else {
            $this->question->multipleChoiceQuestionAnswers->each(function ($answers) use (&$map) {
                $this->answerStruct[$answers->id] = 0;
            });
        }

        $this->shuffledKeys = array_keys($this->answerStruct);
        if ($this->question->subtype != 'ARQ' && $this->question->subtype != 'TrueFalse') {
            shuffle($this->shuffledKeys);
        }

        $this->question->multipleChoiceQuestionAnswers->each(function ($answers) use (&$map) {
            $this->answerText[$answers->id] = $answers->answer;
        });
    }

    public function updatedAnswer($value)
    {
        $this->answerStruct = array_fill_keys(array_keys($this->answerStruct), 0);
        $this->answerStruct[$value] = 1;


        $json = json_encode($this->answerStruct);

        Answer::updateJson($this->answers[$this->question->uuid]['id'], $json);

//        $this->emitUp('updateAnswer', $this->uuid, $this->answerStruct);
    }

    public function render()
    {
        if ($this->question->subtype == 'ARQ') {
            return view('livewire.question.arq-question');
        } elseif ($this->question->subtype == 'TrueFalse') {
            return view('livewire.question.true-false-question');
        }

        return view('livewire.question.multiple-choice-question');
    }
}

<?php

namespace tcCore\Http\Livewire\StudentPlayer\Question;

use tcCore\Answer;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;

class MultipleChoiceQuestion extends TCComponent
{
    use WithAttachments, WithNotepad, withCloseable, WithGroups;

    public $question;

    public $answer = '';

    public $answers;

    public $answerStruct;
    public $shuffledKeys;

    public $number;

    public $arqStructure = [];

    protected $listeners = ['questionUpdated' => 'questionUpdated'];

    public $answerText;

    public $testTakeUuid;


    public function mount()
    {
        $this->arqStructure = \tcCore\MultipleChoiceQuestion::getArqStructure();

        if (!empty(json_decode($this->answers[$this->question->uuid]['answer']))) {
            $this->answerStruct = json_decode($this->answers[$this->question->uuid]['answer'], true);
            if ($this->question->subtype == 'ARQ' || $this->question->subtype == 'TrueFalse') {
                $this->answer = array_keys($this->answerStruct, 1)[0];
            }
        } else {
            $this->question->multipleChoiceQuestionAnswers->each(function ($answers) use (&$map) {
                $this->answerStruct[$answers->id] = 0;
            });
        }

        $this->shuffledKeys = array_keys($this->answerStruct);
        if (!$this->question->isCitoQuestion()) {
            if ($this->question->subtype != 'ARQ' && $this->question->subtype != 'TrueFalse' && !$this->question->fix_order) {
                shuffle($this->shuffledKeys);
            }
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

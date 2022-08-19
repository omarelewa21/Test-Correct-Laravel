<?php

namespace tcCore\Http\Livewire\TestPrint;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Question;

class MultipleChoiceQuestion extends Component
{
    use WithCloseable, WithGroups;

    public $question;

    public $queryString = ['q'];

    public $q;

    public $answer = '';
    public $answered;

    public $answers;

    public $answerStruct = [];
    public $scoreStruct = [];

    public $number;

    public $arqStructure = [];

    public $answerText;

    public $attachment_counters;

    public function mount()
    {
        $this->arqStructure = \tcCore\MultipleChoiceQuestion::getArqStructure();

        $this->question->multipleChoiceQuestionAnswers->each(function ($answers) use (&$map) {
            $this->answerStruct[$answers->id] = ($answers->score>0)?1:0;
            $this->scoreStruct[$answers->id] = $answers->score;
            $this->answerText[$answers->id] = $answers->answer;
        });

        if(!is_null($this->question->belongs_to_groupquestion_id)){
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }


    public function render()
    {
        if ($this->question->subtype == 'ARQ') {
            return view('livewire.test_print.arq-question');
        } elseif ($this->question->subtype == 'TrueFalse') {
            return view('livewire.test_print.true-false-question');

        }

        return view('livewire.test_print.multiple-choice-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        return true;
    }
}

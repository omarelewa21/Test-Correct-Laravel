<?php

namespace tcCore\Http\Livewire\TestPrint;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Question;

class MultipleSelectQuestion extends Component
{
    use WithCloseable, WithGroups;

    public $question;

    public $answer = '';
    public $answered;

    public $answers;

    public $answerStruct = [];
    public $scoreStruct = [];

    public $number;

    public $answerText;
    public $shuffledKeys;


    public function mount()
    {
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
        return view('livewire.test_print.multiple-select-question');
    }


}

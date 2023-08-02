<?php

namespace tcCore\Http\Livewire\AnswerModel;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Question;

class MatchingQuestion extends TCComponent
{
    use  WithNotepad, WithCloseable, WithGroups;

    public $answer;
    public $answered;
    public $question;
    public $number;

    public $answers;
    public $answerStruct = [];

    public $shuffledAnswers;

    public function mount()
    {

        $this->question->loadRelated();

        if(!is_null($this->question->belongs_to_groupquestion_id)){
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    public function render()
    {
        return view('livewire.answer_model.matching-question');
    }


}

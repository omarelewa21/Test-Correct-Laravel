<?php

namespace tcCore\Http\Livewire\AnswerModel;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithGroups;
use tcCore\Question;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithNotepad;

class RankingQuestion extends TCComponent
{
    use WithNotepad, WithCloseable, WithGroups;

    public $uuid;
    public $answer;
    public $question;
    public $number;
    public $answers;
    public $answered;
    public $answerStruct = [];
    public $answerText = [];

    public function mount()
    {
        foreach($this->question->rankingQuestionAnswers as $key => $value) {
            $result[] = (object)['order' => $key + 1, 'value' => $value->id];
            $this->answerText[$value->id] = $value->answer;
        }
        $this->answerStruct = ($result);


        if(!is_null($this->question->belongs_to_groupquestion_id)){
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    public function render()
    {
        return view('livewire.answer_model.ranking-question');
    }


}

<?php

namespace tcCore\Http\Livewire\TestTakeOverviewPreview;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithGroups;
use tcCore\Question;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithNotepad;
use tcCore\RankingQuestionAnswer;

class RankingQuestion extends TCComponent
{
    use WithAttachments, WithNotepad, WithCloseable, WithGroups;

    public $uuid;
    public $answer;
    public $question;
    public $number;
    public $answers;
    public $answered;
    public $answerStruct;
    public $answerText = [];

    public $showQuestionText;

    public function mount()
    {
        $this->answerStruct = (array)json_decode($this->answers[$this->question->uuid]['answer']);

        $result = [];
        if(!$this->answerStruct) {
            foreach($this->question->rankingQuestionAnswers as $key => $value) {
                $result[] = (object)['order' => $key + 1, 'value' => $value->id];
            }
        } else {
            collect($this->answerStruct)->each(function ($value, $key) use (&$result) {
                $result[] = (object)['order' => $value + 1, 'value' => $key];
            })->toArray();
            $this->answer = true;
        }
        $this->answerStruct = ($result);
        collect($this->getRankingQuestionAnswers())->each(function($answers) {
            $this->answerText[$answers->id] = $answers->answer;
        });

        $this->answered = $this->answers[$this->question->uuid]['answered'];

        if(!is_null($this->question->belongs_to_groupquestion_id)){
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    public function render()
    {
        return view('livewire.test_take_overview_preview.ranking-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        return true;
    }

    private function getRankingQuestionAnswers()
    {
        $answersIds = [];
        $answers = [];
        collect($this->answerStruct)->each(function($struct,$key) use (&$answersIds){
            $answersIds[] = (int) $struct->value ;
        });
        $answersIds = array_unique($answersIds);
        sort($answersIds);
        collect($answersIds)->each(function($value,$key) use (&$answers){
            $answer = RankingQuestionAnswer::withTrashed()->find($value);
            if(is_null($answer)){
                return true;
            }
            $answers[] = $answer;
        });
        return $answers;
    }
}

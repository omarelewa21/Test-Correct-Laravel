<?php

namespace tcCore\Http\Livewire\TestTakeOverviewPreview;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\MatchingQuestionAnswer;
use tcCore\Question;

class MatchingQuestion extends TCComponent
{
    use WithAttachments, WithNotepad, WithCloseable, WithGroups;

    public $answer;
    public $answered;
    public $question;
    public $number;

    public $answers;
    public $answerStruct;

    public $shuffledAnswers;

    public $showQuestionText;

    public function mount()
    {
        $this->question->loadRelated();
        $this->answered = $this->answers[$this->question->uuid]['answered'];
        $this->answerStruct = json_decode($this->answers[$this->question->uuid]['answer'], true);

        if ($this->answers[$this->question->uuid]['answer']) {
            $this->answer = true;
        }

        if(!$this->answerStruct) {
            foreach($this->question->matchingQuestionAnswers as $key => $value) {
                if ($value->correct_answer_id !== null) {
                    $this->answerStruct[$value->id] = "";
                }
            }
        }
        $this->shuffledAnswers = $this->getMatchingQuestionAnswers();
        if(!is_null($this->question->belongs_to_groupquestion_id)){
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    public function render()
    {
        return view('livewire.test_take_overview_preview.matching-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        $givedAnswers = count(array_filter($this->answerStruct));
        $options = count($this->answerStruct);
        return $options === $givedAnswers;
    }

    private function getMatchingQuestionAnswers()
    {
        $matchingQuestionAnswersIds = [];
        $matchingQuestionAnswers = [];
        collect($this->answerStruct)
            ->reject(fn($i, $key) => $key == 'order' )
            ->each(function($key,$value) use (&$matchingQuestionAnswersIds){
            $matchingQuestionAnswersIds[] = (int) $key;
            $matchingQuestionAnswersIds[] = (int) $value ;
        });
        $matchingQuestionAnswersIds = array_unique($matchingQuestionAnswersIds);
        if(in_array(0,$matchingQuestionAnswersIds)){
            $matchingQuestionAnswersIds = $this->repairForMissingAnswers($matchingQuestionAnswersIds);
        }
        sort($matchingQuestionAnswersIds);
        collect($matchingQuestionAnswersIds)
            ->reject(fn($i, $key) => $key == 'order' )
            ->each(function($value,$key) use (&$matchingQuestionAnswers){
            $matchingQuestionAnswer = MatchingQuestionAnswer::withTrashed()->find($value);
            if(is_null($matchingQuestionAnswer)){
                return true;
            }
            $matchingQuestionAnswers[] = $matchingQuestionAnswer;
        });
        return $matchingQuestionAnswers;
    }

    private function repairForMissingAnswers($matchingQuestionAnswersIds)
    {
        $collection = collect($matchingQuestionAnswersIds);
        $keyArray = [];
        $collection->each(function($value,$key) use (&$matchingQuestionAnswersIds,&$keyArray){
            $matchingQuestionAnswer = MatchingQuestionAnswer::withTrashed()->find($value);
            if(is_null($matchingQuestionAnswer)){
                $keyArray[] = $key;
                return true;
            }
            if(is_null($matchingQuestionAnswer->correct_answer_id)){
                return true;
            }
            if(in_array($matchingQuestionAnswer->correct_answer_id,$matchingQuestionAnswersIds)){
                return true;
            }
            $matchingQuestionAnswersIds[] = $matchingQuestionAnswer->correct_answer_id;
        });
        collect($keyArray)->each(function($value,$key) use (&$matchingQuestionAnswersIds){
            unset($matchingQuestionAnswersIds[$value]);
        });
        return $matchingQuestionAnswersIds;
    }
}

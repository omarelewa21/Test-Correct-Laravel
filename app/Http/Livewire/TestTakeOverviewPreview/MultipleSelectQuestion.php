<?php

namespace tcCore\Http\Livewire\TestTakeOverviewPreview;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\MultipleChoiceQuestionAnswer;
use tcCore\Question;

class MultipleSelectQuestion extends TCComponent
{
    use WithCloseable, WithGroups;

    public $question;

    public $answer = '';
    public $answered;

    public $answers;

    public $answerStruct;

    public $number;

    public $answerText;
    public $shuffledKeys;

    public $showQuestionText;

    public function mount()
    {
        if (!empty(json_decode($this->answers[$this->question->uuid]['answer']))) {
            $this->answerStruct = json_decode($this->answers[$this->question->uuid]['answer'], true);
            $this->answer = 'answered';
        } else {
            $this->question->multipleChoiceQuestionAnswers->each(function ($answers) use (&$map) {
                $this->answerStruct[$answers->id] = 0;
            });
        }


        $this->shuffledKeys = array_keys($this->answerStruct);
//        if (!$this->question->isCitoQuestion()) {
//            if ($this->question->subtype != 'ARQ' && $this->question->subtype != 'TrueFalse') {
//                shuffle($this->shuffledKeys);
//            }
//        }

        collect($this->getMultipleSelectQuestionAnswers())->each(function ($answers) use (&$map) {
            $this->answerText[$answers->id] = $answers->answer;
        });

        $this->answered = $this->answers[$this->question->uuid]['answered'];

        if(!is_null($this->question->belongs_to_groupquestion_id)){
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    public function render()
    {
        return view('livewire.test_take_overview_preview.multiple-select-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        $selectedAnswers = count(array_keys($this->answerStruct, 1));
        return $this->question->selectable_answers === $selectedAnswers;
    }

    private function getMultipleSelectQuestionAnswers()
    {
        $answersIds = [];
        $answers = [];
        collect($this->answerStruct)->each(function($value,$key) use (&$answersIds){
            $answersIds[] = (int) $key ;
        });
        $answersIds = array_unique($answersIds);
        sort($answersIds);
        collect($answersIds)->each(function($value,$key) use (&$answers){
            $answer = MultipleChoiceQuestionAnswer::withTrashed()->find($value);
            if(is_null($answer)){
                return true;
            }
            $answers[] = $answer;
        });
        return $answers;
    }
}

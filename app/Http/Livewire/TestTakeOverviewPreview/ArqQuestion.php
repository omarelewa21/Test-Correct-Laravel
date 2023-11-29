<?php

namespace tcCore\Http\Livewire\TestTakeOverviewPreview;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\MultipleChoiceQuestionAnswer;
use tcCore\Question;

class ArqQuestion extends TCComponent
{
    use WithCloseable, WithGroups;

    protected $listeners = ['questionUpdated' => 'questionUpdated'];

    public $uuid;

    public $question;

    public $showQuestionText;

    public function questionUpdated($uuid)
    {
        $this->uuid = $uuid;
    }

    public function render()
    {
        $question = Question::whereUuid($this->uuid)->first();
        return view('livewire.test_take_overview_preview.arq-question');
    }

    private function getMultipleChoiceQuestionAnswers()
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

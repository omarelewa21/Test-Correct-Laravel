<?php

namespace tcCore\Http\Livewire\TestTakeOverviewPreview;

use tcCore\Answer;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\MultipleChoiceQuestionAnswer;
use tcCore\Question;

class MultipleChoiceQuestion extends TCComponent
{
    use WithCloseable, WithGroups;

    public $question;

    public $queryString = ['q'];

    public $q;

    public $answer = '';
    public $answered;

    public $answers;

    public $answerStruct;
    public $shuffledKeys;

    public $number;

    public $arqStructure = [];

    public $answerText;

    public $showQuestionText;

    public function mount()
    {
        $this->arqStructure = \tcCore\MultipleChoiceQuestion::getArqStructure();

        if (!empty(json_decode($this->answers[$this->question->uuid]['answer']))) {
            $this->answerStruct = json_decode($this->answers[$this->question->uuid]['answer'], true);
            $this->answer = array_keys($this->answerStruct, 1)[0];
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


        collect($this->getMultipleChoiceQuestionAnswers())->each(function ($answers) use (&$map) {
            $this->answerText[$answers->id] = $answers->answer;
        });

        $this->answered = $this->answers[$this->question->uuid]['answered'];

        if(!is_null($this->question->belongs_to_groupquestion_id)){
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    public function updatedAnswer($value)
    {
        $this->answerStruct = array_fill_keys(array_keys($this->answerStruct), 0);
        $this->answerStruct[$value] = 1;

        $json = json_encode($this->answerStruct);

        Answer::where([
            ['id', $this->answers[$this->question->uuid]['id']],
            ['question_id', $this->question->id],
        ])->update(['json' => $json]);


//        $this->emitUp('updateAnswer', $this->uuid, $this->answerStruct);
    }

    public function render()
    {
        if ($this->question->subtype == 'ARQ') {
            return view('livewire.test_take_overview_preview.arq-question');
        } elseif ($this->question->subtype == 'TrueFalse') {
            return view('livewire.test_take_overview_preview.true-false-question');

        }

        return view('livewire.test_take_overview_preview.multiple-choice-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        return true;
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

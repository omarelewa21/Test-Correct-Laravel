<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithQuestionTimer;
use tcCore\Http\Traits\WithUpdatingHandling;

class RankingQuestion extends Component
{
    use WithAttachments, WithNotepad, withCloseable, WithGroups, WithUpdatingHandling;

    public $uuid;
    public $answer;
    public $question;
    public $number;
    public $answers;
    public $answerStruct;
    public $testTakeUuid;
    public $answerText = [];

    public function mount()
    {
        $this->answerStruct = (array)json_decode($this->answers[$this->question->uuid]['answer']);

        $result = [];
        if(empty($this->answerStruct)) {
            foreach($this->question->rankingQuestionAnswers as $key => $value) {
                $result[] = (object)['order' => $key + 1, 'value' => $value->id];
            }
            shuffle($result);
        } else {
            collect($this->answerStruct)->each(function ($value, $key) use (&$result) {
                $result[] = (object)['order' => $value + 1, 'value' => $key];
            })->toArray();
        }
        $this->answerStruct = ($result);

        collect($this->question->rankingQuestionAnswers->each(function($answers) use (&$map) {
             $this->answerText[$answers->id] = $answers->answer;
        }));
    }

    public function updateOrder($value)
    {
        $this->answerStruct = $value;

        $result = (object)[];

        collect($value)->each(function ($object, $key) use (&$result) {
            $result->{$object['value']} = $object['order']-1;
        });

        $json = json_encode($result);

        Answer::updateJson($this->answers[$this->question->uuid]['id'], $json);

        $this->createAnswerStruct();
        $this->emitTo('question.navigation','current-question-answered', $this->number);
    }


    public function render()
    {
        $this->dispatchDragItemWidth();
        return view('livewire.question.ranking-question');
    }

    public function createAnswerStruct()
    {
        $result = [];

        collect($this->answerStruct)->each(function ($value, $key) use (&$result) {
            $result[] = (object)['order' => $key + 1, 'value' => $value['value']];
        })->toArray();

        $this->answerStruct = ($result);
    }

    public function hydrateAnswerStruct()
    {
        $this->createAnswerStruct();
    }

    public function dispatchDragItemWidth()
    {
        $this->dispatchBrowserEvent('add-width-to-drag-item');
    }
}

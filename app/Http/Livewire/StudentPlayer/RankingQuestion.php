<?php

namespace tcCore\Http\Livewire\StudentPlayer;

use tcCore\Answer;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithQuestionTimer;

abstract class RankingQuestion extends TCComponent
{
    use withCloseable;

    public $uuid;
    public $answer;
    public $question;
    public $number;
    public $answers;
    public $answerStruct;
    public $answerText = [];

    public function mount()
    {
        $this->setAnswerStruct();

        $this->setAnswerTexts();
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

    protected function setAnswerStruct(): void
    {
        $this->answerStruct = (array)json_decode($this->answers[$this->question->uuid]['answer']);

        $result = [];
        if (empty($this->answerStruct)) {
            foreach ($this->question->rankingQuestionAnswers as $key => $value) {
                $result[] = (object)['order' => $key + 1, 'value' => $value->id];
            }
            shuffle($result);
        } else {
            collect($this->answerStruct)->each(function ($value, $key) use (&$result) {
                $result[] = (object)['order' => $value + 1, 'value' => $key];
            })->toArray();
        }
        $this->answerStruct = ($result);
    }

    protected function setAnswerTexts(): void
    {
        collect(
            $this->question->rankingQuestionAnswers->each(function ($answers) use (&$map) {
                $this->answerText[$answers->id] = $answers->answer;
            })
        );
    }
}

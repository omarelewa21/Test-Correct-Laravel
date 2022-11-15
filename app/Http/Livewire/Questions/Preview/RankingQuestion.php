<?php

namespace tcCore\Http\Livewire\Questions\Preview;

use Livewire\Component;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithPreviewAttachments;
use tcCore\Http\Traits\WithPreviewGroups;

class RankingQuestion extends Component
{
    use WithPreviewAttachments, WithNotepad, withCloseable, WithPreviewGroups;

    public $uuid;
    public $answer;
    public $question;
    public $testId;
    public $number;
    public $answers;
    public $answerStruct;
    public $answerText = [];

    public function questionUpdated($uuid, $answer)
    {
        $this->uuid = $uuid;
        $this->answer = $answer;

    }

    public function mount()
    {
        $result = [];

        foreach($this->question->rankingQuestionAnswers as $key => $value) {
            $result[] = (object)['order' => $key + 1, 'value' => $value->id];
        }
        shuffle($result);

        $this->answerStruct = ($result);

        collect($this->question->rankingQuestionAnswers->each(function($answers) use (&$map) {
             $this->answerText[$answers->id] = $answers->answer;
        }));
    }


    public function render()
    {
        return view('livewire.questions.preview.ranking-question');
    }

    public function updateOrder($value)
    {
        $this->answerStruct = $value;
        $this->createAnswerStruct();
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
}

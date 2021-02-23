<?php

namespace tcCore\Http\Livewire\Overview;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithNotepad;

class RankingQuestion extends Component
{
    use WithAttachments, WithNotepad;

    public $uuid;
    public $answer;
    public $question;
    public $number;
    public $answers;
    public $answerStruct;
    public $answerText = [];

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
        }
        $this->answerStruct = ($result);


        collect($this->question->rankingQuestionAnswers->each(function($answers) use (&$map) {
            $this->answerText[$answers->id] = $answers->answer;
        }));

        if ($this->answers[$this->question->uuid]['answer']) {
            $this->answer = true;
        }
    }

    public function render()
    {
        return view('livewire.overview.ranking-question');
    }

}

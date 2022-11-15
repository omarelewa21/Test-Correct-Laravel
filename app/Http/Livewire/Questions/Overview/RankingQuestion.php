<?php

namespace tcCore\Http\Livewire\Questions\Overview;

use Livewire\Component;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Question;

class RankingQuestion extends Component
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

    public function mount()
    {
        $this->answerStruct = (array)json_decode($this->answers[$this->question->uuid]['answer']);

        $result = [];
        if(!$this->answerStruct) {
            foreach($this->question->rankingQuestionAnswers as $key => $value) {
                $result[] = (object)['order' => $key + 1, 'value' => $value->id];
            }
            shuffle($result);
        } else {
            collect($this->answerStruct)->each(function ($value, $key) use (&$result) {
                $result[] = (object)['order' => $value + 1, 'value' => $key];
            })->toArray();
            $this->answer = true;
        }
        $this->answerStruct = ($result);

        collect($this->question->rankingQuestionAnswers->each(function($answers) use (&$map) {
            $this->answerText[$answers->id] = $answers->answer;
        }));

        $this->answered = $this->answers[$this->question->uuid]['answered'];

        if(!is_null($this->question->belongs_to_groupquestion_id)){
            $this->question->groupQuestion = Question::find($this->question->belongs_to_groupquestion_id);
        }
    }

    public function render()
    {
        return view('livewire.overview.ranking-question');
    }

    public function isQuestionFullyAnswered(): bool
    {
        return true;
    }
}

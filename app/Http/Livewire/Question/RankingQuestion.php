<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;
use tcCore\Answer;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Question;

class RankingQuestion extends Component
{
    use WithAttachments, WithNotepad;

    public $uuid;
    public $answer;
    public $question;
    public $number;
    public $answers;
    public $answerStruct;

    public function questionUpdated($uuid, $answer)
    {
        $this->uuid = $uuid;
        $this->answer = $answer;

    }

    public function mount()
    {
        $this->answerStruct = (array) json_decode($this->answers[$this->question->uuid]['answer']);

        $result = [];

        collect($this->answerStruct)->each(function($value, $key) use (&$result) {
            $result[] =  (object) ['order' => $value+1, 'value' => $key];
        })->toArray();

        $this->answerStruct = ($result);
    }

    public function updateOrder($value)
    {
        $result = (object) [];

        collect($value)->each(function($object, $key) use (&$result) {
            $result->{$object['order']} = $object['value'];
        });

        $json = json_encode($result);

        dd($json);
        Answer::where([
            ['id', $this->answers[$this->question->uuid]['id']],
            ['question_id', $this->question->id],
        ])->update(['json' => $json]);

    }


    public function render()
    {
        return view('livewire.question.ranking-question');
    }
}

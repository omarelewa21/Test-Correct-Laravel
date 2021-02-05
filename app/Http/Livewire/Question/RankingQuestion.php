<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;
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

    public function questionUpdated($uuid, $answer)
    {
        $this->uuid = $uuid;
        $this->answer = $answer;

    }

    public function mount(){
        $this->question->loadRelated();
    }

    public function updateOrder($value)
    {
//        dd($value);
//        $this->emitUp('updateAnswer', $this->uuid, $value);
    }


    public function render()
    {
        return view('livewire.question.ranking-question');
    }
}

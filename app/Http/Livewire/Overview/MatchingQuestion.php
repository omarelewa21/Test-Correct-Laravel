<?php

namespace tcCore\Http\Livewire\Overview;

use Livewire\Component;
use tcCore\Question;

class MatchingQuestion extends Component
{
    protected $listeners = ['questionUpdated' => 'questionUpdated'];

    public $answer;
    public $question;
    public $number;

    public function questionUpdated($uuid, $answer)
    {
        $this->uuid = $uuid;
        $this->answer = $answer;
    }

    public function updatedAnswer($value)
    {
//        $this->emitUp('updateAnswer', $this->uuid, $value);
    }


    public function render()
    {
        return view('livewire.overview.matching-question');
    }
}

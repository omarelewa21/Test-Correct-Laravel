<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;
use tcCore\Question;

class InfoScreenQuestion extends Component
{
    protected $listeners = ['questionUpdated' => 'questionUpdated'];

    public $uuid;

    public function questionUpdated($uuid)
    {
        $this->uuid = $uuid;
    }

    public function render()
    {
        $question = Question::whereUuid($this->uuid)->first();
        return view('livewire.question.info-screen-question', compact('question'));
    }
}

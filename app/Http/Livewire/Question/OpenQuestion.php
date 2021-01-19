<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;
use tcCore\Question;

class OpenQuestion extends Component
{
    public $uuid;
    protected $listeners = ['questionUpdated' => '$refresh'];
    public $answer = 'me';

    public function render()
    {
//        dd($this->uuid);
        $question = Question::whereUuid($this->uuid)->first();
        return view('livewire.question.open-question', compact('question'));
    }
}

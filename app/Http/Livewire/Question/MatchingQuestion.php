<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;
use tcCore\Question;

class MatchingQuestion extends Component
{
    protected $listeners = ['questionUpdated' => '$refresh'];

    public $uuid;
    public function render()
    {
        $question = Question::whereUuid($this->uuid)->first();
        return view('livewire.question.matching-question', compact('question'));
    }
}

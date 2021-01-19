<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;
use tcCore\Question;

class RankingQuestion extends Component
{
    protected $listeners = ['questionUpdated' => '$refresh'];

    public $uuid;

    public function render()
    {
        $question = Question::whereUuid($this->uuid)->first();

        return view('livewire.question.ranking-question', compact('question'));
    }
}

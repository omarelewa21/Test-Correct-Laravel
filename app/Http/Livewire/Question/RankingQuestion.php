<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;

class RankingQuestion extends Component
{
    public $question;
    public function render()
    {
        return view('livewire.question.ranking-question');
    }
}

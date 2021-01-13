<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;

class MatchingQuestion extends Component
{
    public $question;
    public function render()
    {
        return view('livewire.question.matching-question');
    }
}

<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;

class MultipleChoiceQuestion extends Component
{
    public $question;

    public function render()
    {
        return view('livewire.question.multiple-choice-question');
    }
}

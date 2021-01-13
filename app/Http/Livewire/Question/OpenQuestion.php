<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;

class OpenQuestion extends Component
{
    public $question;

    public function render()
    {
        return view('livewire.question.open-question');
    }
}

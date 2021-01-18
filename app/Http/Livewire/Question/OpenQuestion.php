<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;

class OpenQuestion extends Component
{
    public $question;
    protected $listeners = ['questionUpdated' => '$refresh'];

    public function render()
    {
        return view('livewire.question.open-question');
    }
}

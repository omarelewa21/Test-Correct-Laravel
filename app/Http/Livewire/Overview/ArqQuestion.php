<?php

namespace tcCore\Http\Livewire\Overview;

use Livewire\Component;
use tcCore\Question;

class ArqQuestion extends Component
{
    protected $listeners = ['questionUpdated' => 'questionUpdated'];

    public $uuid;

    public $question;

    public function questionUpdated($uuid)
    {
        $this->uuid = $uuid;
    }

    public function render()
    {
        $question = Question::whereUuid($this->uuid)->first();
        return view('livewire.overview.arq-question');
    }
}

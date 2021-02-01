<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Question;

class ArqQuestion extends Component
{
    use WithAttachments, WithNotepad;

    protected $listeners = ['questionUpdated' => 'questionUpdated'];

    public $uuid;

    public $question;

    public $queryString = ['q'];

    public $q;

    public function questionUpdated($uuid)
    {
        $this->uuid = $uuid;
    }

    public function render()
    {
        $question = Question::whereUuid($this->uuid)->first();
        return view('livewire.question.arq-question');
    }
}

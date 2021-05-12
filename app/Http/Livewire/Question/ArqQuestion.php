<?php

namespace tcCore\Http\Livewire\Question;

use Livewire\Component;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithUpdatingHandling;
use tcCore\Question;

class ArqQuestion extends Component
{
    use WithAttachments, WithNotepad, withCloseable, WithGroups, WithUpdatingHandling;

    public $uuid;

    public $question;

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

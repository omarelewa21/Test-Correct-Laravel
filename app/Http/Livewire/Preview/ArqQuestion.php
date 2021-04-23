<?php

namespace tcCore\Http\Livewire\Preview;

use Livewire\Component;
use tcCore\Http\Traits\WithPreviewAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Question;

class ArqQuestion extends Component
{
    use WithPreviewAttachments, WithNotepad, withCloseable, WithGroups;

    public $uuid;

    public $question;

    public function questionUpdated($uuid)
    {
        $this->uuid = $uuid;
    }

    public function render()
    {
        $question = Question::whereUuid($this->uuid)->first();
        return view('livewire.preview.arq-question');
    }
}

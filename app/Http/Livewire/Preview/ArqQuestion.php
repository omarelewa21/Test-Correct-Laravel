<?php

namespace tcCore\Http\Livewire\Preview;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithPreviewAttachments;
use tcCore\Http\Traits\WithPreviewGroups;
use tcCore\Question;

class ArqQuestion extends TCComponent
{
    use WithPreviewAttachments, WithNotepad, withCloseable, WithPreviewGroups;

    public $uuid;
    public $question;
    public $testId;

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

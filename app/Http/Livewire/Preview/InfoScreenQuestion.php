<?php

namespace tcCore\Http\Livewire\Preview;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithPreviewAttachments;
use tcCore\Http\Traits\WithPreviewGroups;

class InfoScreenQuestion extends TCComponent
{
    use WithPreviewAttachments, WithNotepad, withCloseable, WithPreviewGroups;

    public $question;
    public $testId;

    public $number;

    public $answers;

    public $answer = '';

    public function render()
    {
        return view('livewire.preview.info-screen-question');
    }

    public function markAsSeen($questionUuid)
    {

    }
}

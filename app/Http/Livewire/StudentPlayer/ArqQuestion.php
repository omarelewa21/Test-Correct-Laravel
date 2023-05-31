<?php

namespace tcCore\Http\Livewire\StudentPlayer;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Question;

abstract class ArqQuestion extends TCComponent
{
    use withCloseable;

    public $uuid;

    public $question;

    public function questionUpdated($uuid)
    {
        $this->uuid = $uuid;
    }
}

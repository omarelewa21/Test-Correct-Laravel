<?php

namespace tcCore\Http\Livewire\StudentPlayer;

use tcCore\Answer;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithQuestionTimer;

abstract class InfoScreenQuestion extends TCComponent
{
    use withCloseable;

    public $question;
    public $number;
    public $answers;
}

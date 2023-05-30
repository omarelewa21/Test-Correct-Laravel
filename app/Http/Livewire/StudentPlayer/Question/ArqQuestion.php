<?php

namespace tcCore\Http\Livewire\StudentPlayer\Question;

use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Livewire\StudentPlayer\ArqQuestion as AbstractArqQuestion;

class ArqQuestion extends AbstractArqQuestion
{
    use WithAttachments;
    use WithGroups;
    use WithNotepad;
}

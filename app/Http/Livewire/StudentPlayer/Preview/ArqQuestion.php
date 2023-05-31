<?php

namespace tcCore\Http\Livewire\StudentPlayer\Preview;

use tcCore\Http\Livewire\StudentPlayer\ArqQuestion as AbstractArqQuestion;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithPreviewAttachments;
use tcCore\Http\Traits\WithPreviewGroups;

class ArqQuestion extends AbstractArqQuestion
{
    use WithNotepad;
    use WithPreviewAttachments;
    use WithPreviewGroups;

    public $testId;
    public function render()
    {
        return view('livewire.student-player.preview.arq-question');
    }
}

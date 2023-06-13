<?php

namespace tcCore\Http\Livewire\StudentPlayer\Preview;

use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithPreviewAttachments;
use tcCore\Http\Traits\WithPreviewGroups;
use tcCore\Http\Livewire\StudentPlayer\OpenQuestion as AbstractOpenQuestion;

class OpenQuestion extends AbstractOpenQuestion
{
    use WithNotepad;
    use WithPreviewAttachments;
    use WithPreviewGroups;

    public $testId;

    public function updatedAnswer($value) {}

    public function render()
    {
        return view('livewire.student-player.preview.open-question');
    }
}

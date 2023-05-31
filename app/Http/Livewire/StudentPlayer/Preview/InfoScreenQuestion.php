<?php

namespace tcCore\Http\Livewire\StudentPlayer\Preview;

use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithPreviewAttachments;
use tcCore\Http\Traits\WithPreviewGroups;
use tcCore\Http\Livewire\StudentPlayer\InfoScreenQuestion as AbstractInfoScreenQuestion;

class InfoScreenQuestion extends AbstractInfoScreenQuestion
{
    use WithNotepad;
    use WithPreviewAttachments;
    use WithPreviewGroups;

    public $answer = '';
    public $testId;

    public function render()
    {
        return view('livewire.student-player.preview.info-screen-question');
    }

    public function markAsSeen() {}
}

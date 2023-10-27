<?php

namespace tcCore\Http\Livewire\StudentPlayer\Preview;

use tcCore\Http\Livewire\StudentPlayer\RelationQuestion as AbstractRelationQuestion;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithPreviewAttachments;
use tcCore\Http\Traits\WithPreviewGroups;

class RelationQuestion extends AbstractRelationQuestion
{
    use WithNotepad;
    use WithPreviewAttachments;
    use WithPreviewGroups;
    public function render()
    {
        return view('livewire.student-player.preview.relation-question');
    }

}
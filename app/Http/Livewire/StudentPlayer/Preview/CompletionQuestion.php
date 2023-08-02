<?php

namespace tcCore\Http\Livewire\StudentPlayer\Preview;

use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithPreviewAttachments;
use tcCore\Http\Traits\WithPreviewGroups;
use tcCore\Http\Livewire\StudentPlayer\CompletionQuestion as AbstractCompletionQuestion;

class CompletionQuestion extends AbstractCompletionQuestion
{
    use WithNotepad;
    use WithPreviewAttachments;
    use WithPreviewGroups;

    public $testId;

    public function updatedAnswer($value, $field) {}

    public function render()
    {
        return view('livewire.student-player.preview.completion-question');
    }
}

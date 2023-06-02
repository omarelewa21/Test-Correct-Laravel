<?php

namespace tcCore\Http\Livewire\StudentPlayer\Preview;

use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithPreviewAttachments;
use tcCore\Http\Traits\WithPreviewGroups;
use tcCore\Http\Livewire\StudentPlayer\DrawingQuestion as AbstractDrawingQuestion;

class DrawingQuestion extends AbstractDrawingQuestion
{
    use WithNotepad;
    use WithPreviewAttachments;
    use WithPreviewGroups;

    public $testId;

    public function updatedAnswer($value)
    {
        $this->drawingModalOpened = false;
    }

    public function render()
    {
        return view('livewire.student-player.preview.drawing-question');
    }

    public function handleUpdateDrawingData() {}
}

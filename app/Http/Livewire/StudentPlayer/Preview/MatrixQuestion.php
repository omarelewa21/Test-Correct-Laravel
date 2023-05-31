<?php

namespace tcCore\Http\Livewire\StudentPlayer\Preview;

use Illuminate\Support\Str;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithPreviewAttachments;
use tcCore\Http\Traits\WithPreviewGroups;
use tcCore\Http\Livewire\StudentPlayer\MatrixQuestion as AbstractMatrixQuestion;

class MatrixQuestion extends AbstractMatrixQuestion
{
    use WithNotepad;
    use WithPreviewAttachments;
    use WithPreviewGroups;

    public $testId;
    public function render()
    {
        return view('livewire.student-player.preview.matrix-question');
    }

    public function updatingAnswer($value)
    {
        $answerIds = Str::of($value)->explode(':');
        $this->answerStruct[$answerIds[0]] = $answerIds[1];
    }
}

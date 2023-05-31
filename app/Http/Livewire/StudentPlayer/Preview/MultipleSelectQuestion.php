<?php

namespace tcCore\Http\Livewire\StudentPlayer\Preview;

use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithPreviewAttachments;
use tcCore\Http\Traits\WithPreviewGroups;
use tcCore\Http\Livewire\StudentPlayer\MultipleSelectQuestion as AbstractMultipleSelectQuestionAlias;

class MultipleSelectQuestion extends AbstractMultipleSelectQuestionAlias
{
    use WithNotepad;
    use WithPreviewAttachments;
    use WithPreviewGroups;

    public $testId;

    public function render()
    {
        return view('livewire.student-player.preview.multiple-select-question');
    }

    protected function setAnswerStruct($whenHasAnswerCallback = null): void
    {
        $this->setDefaultStruct();
    }
}

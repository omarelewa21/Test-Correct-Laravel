<?php

namespace tcCore\Http\Livewire\StudentPlayer\Preview;

use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithPreviewAttachments;
use tcCore\Http\Traits\WithPreviewGroups;
use tcCore\Http\Livewire\StudentPlayer\MultipleChoiceQuestion as AbstractMultipleChoiceQuestionAlias;

class MultipleChoiceQuestion extends AbstractMultipleChoiceQuestionAlias
{
    use WithNotepad;
    use WithPreviewAttachments;
    use WithPreviewGroups;

    public $testId;

    public function render()
    {
        return view('livewire.student-player.preview.' . $this->getTemplateName());
    }

    protected function setAnswerStruct($whenHasAnswerCallback = null): void
    {
        $this->setDefaultStruct();
    }
}

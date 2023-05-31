<?php

namespace tcCore\Http\Livewire\StudentPlayer\Preview;

use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithPreviewAttachments;
use tcCore\Http\Traits\WithPreviewGroups;
use tcCore\Http\Livewire\StudentPlayer\MatchingQuestion as AbstractMatchingQuestionAlias;

class MatchingQuestion extends AbstractMatchingQuestionAlias
{
    use WithNotepad;
    use WithPreviewAttachments;
    use WithPreviewGroups;

    public $testId;

    protected function matchingUpdateValueOrder($dbstring, $values, $struct = null)
    {
        return parent::matchingUpdateValueOrder($dbstring, $values, $this->answerStruct);
    }

    public function updateOrder($values)
    {
        $this->answerStruct = parent::updateOrder($values);
    }


    public function render()
    {
        return view('livewire.student-player.preview.matching-question');
    }

}

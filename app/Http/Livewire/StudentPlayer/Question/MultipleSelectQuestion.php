<?php

namespace tcCore\Http\Livewire\StudentPlayer\Question;

use tcCore\Answer;
use tcCore\Http\Livewire\TCComponent;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithCloseable;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Livewire\StudentPlayer\MultipleSelectQuestion as AbstractMultipleSelectQuestion;

class MultipleSelectQuestion extends AbstractMultipleSelectQuestion
{
    use WithAttachments;
    use WithGroups;
    use WithNotepad;

    public $testTakeUuid;

    public function updatedAnswer($value)
    {
        parent::updatedAnswer($value);

        $json = json_encode($this->answerStruct);

        Answer::updateJson($this->answers[$this->question->uuid]['id'], $json);
    }

    public function render()
    {
        return view('livewire.student-player.question.multiple-select-question');
    }
}

<?php

namespace tcCore\Http\Livewire\StudentPlayer\Question;

use tcCore\Answer;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Livewire\StudentPlayer\MultipleChoiceQuestion as AbstractMultipleChoiceQuestionAlias;

class MultipleChoiceQuestion extends AbstractMultipleChoiceQuestionAlias
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
        return view('livewire.student-player.preview.' . $this->getTemplateName());
    }

    protected function setAnswerStruct($whenHasAnswerCallback = null): void
    {
        parent::setAnswerStruct(function () {
            if ($this->question->subtype == 'ARQ' || $this->question->subtype == 'TrueFalse') {
                $this->answer = array_keys($this->answerStruct, 1)[0];
            }
        });
    }
}

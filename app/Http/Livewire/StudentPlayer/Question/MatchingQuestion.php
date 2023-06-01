<?php

namespace tcCore\Http\Livewire\StudentPlayer\Question;

use tcCore\Answer;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Livewire\StudentPlayer\MatchingQuestion as AbstractMatchingQuestion;

class MatchingQuestion extends AbstractMatchingQuestion
{
    use WithAttachments;
    use WithGroups;
    use WithNotepad;

    public $testTakeUuid;

    protected function matchingUpdateValueOrder($dbstring, $values, $struct = null)
    {
        return parent::matchingUpdateValueOrder(
            $dbstring,
            $values,
            json_decode(
                Answer::find($this->answers[$this->question->uuid]['id'])->json,
                true
            )
        );
    }

    public function updateOrder($values)
    {
        $this->answerStruct = parent::updateOrder($values);
        $json = $this->getJsonToStore($this->answerStruct);

        Answer::updateJson($this->answers[$this->question->uuid]['id'], $json);

        $this->emitTo('student-player.question.navigation', 'current-question-answered', $this->number);
    }

    public function render()
    {
        return view('livewire.student-player.question.matching-question');
    }

    protected function getJsonToStore(array $answerObject): string
    {
        return json_encode($answerObject);
    }
}

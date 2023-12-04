<?php

namespace tcCore\Http\Livewire\StudentPlayer\Question;

use Illuminate\Support\Str;
use tcCore\Answer;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Livewire\StudentPlayer\RelationQuestion as AbstractRelationQuestion;

class RelationQuestion extends AbstractRelationQuestion
{
    use WithAttachments;
    use WithGroups;
    use WithNotepad;

    public $testTakeUuid;
    
    public function render()
    {
        return view('livewire.student-player.question.relation-question');
    }

    public function updatedAnswerStruct($value)
    {
        $json = json_encode($this->answerStruct);

        Answer::updateJson($this->answers[$this->question->uuid]['id'], $json);
    }
}

<?php

namespace tcCore\Http\Livewire\StudentPlayer\Question;

use tcCore\Answer;
use tcCore\Http\Traits\WithGroups;
use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithAttachments;
use tcCore\Http\Livewire\StudentPlayer\RankingQuestion as AbstractRankingQuestion;

class RankingQuestion extends AbstractRankingQuestion
{
    use WithAttachments;
    use WithGroups;
    use WithNotepad;

    public $testTakeUuid;

    public function updateOrder($value)
    {
        $this->answerStruct = $value;

        $result = (object)[];

        collect($value)->each(function ($object, $key) use (&$result) {
            $result->{$object['value']} = $object['order'] - 1;
        });

        $json = json_encode($result);

        Answer::updateJson($this->answers[$this->question->uuid]['id'], $json);

        $this->createAnswerStruct();
        $this->emitTo('student-player.question.navigation', 'current-question-answered', $this->number);
    }


    public function render()
    {
        $this->dispatchDragItemWidth();
        return view('livewire.student-player.question.ranking-question');
    }

    public function dispatchDragItemWidth()
    {
        $this->dispatchBrowserEvent('add-width-to-drag-item');
    }
}

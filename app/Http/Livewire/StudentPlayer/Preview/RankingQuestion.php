<?php

namespace tcCore\Http\Livewire\StudentPlayer\Preview;

use tcCore\Http\Traits\WithNotepad;
use tcCore\Http\Traits\WithPreviewAttachments;
use tcCore\Http\Traits\WithPreviewGroups;
use tcCore\Http\Livewire\StudentPlayer\RankingQuestion as AbstractRankingQuestion;

class RankingQuestion extends AbstractRankingQuestion
{
    use WithNotepad;
    use WithPreviewAttachments;
    use WithPreviewGroups;

    public $testId;

    public function render()
    {
        return view('livewire.student-player.preview.ranking-question');
    }

    public function updateOrder($value)
    {
        $this->answerStruct = $value;
        $this->createAnswerStruct();
    }

    protected function setAnswerStruct(): void
    {
        $result = [];
        foreach ($this->question->rankingQuestionAnswers as $key => $value) {
            $result[] = (object)['order' => $key + 1, 'value' => $value->id];
        }
        shuffle($result);

        $this->answerStruct = ($result);
    }
}

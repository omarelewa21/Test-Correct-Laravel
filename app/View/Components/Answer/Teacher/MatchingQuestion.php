<?php

namespace tcCore\View\Components\Answer\Teacher;

use Illuminate\Support\Collection;

class MatchingQuestion extends QuestionComponent
{
    public Collection $answerStruct;

    protected function setAnswerStruct($question): void
    {
        $this->answerStruct = $question->getCorrectAnswerStructure()
            ->groupBy(fn($answer) => $answer->correct_answer_id ?? $answer->id);
    }
}
<?php

namespace tcCore\View\Components\Answer\Teacher;

class MatchingQuestion extends QuestionComponent
{
    public $answerStruct;

    protected function setAnswerStruct($question): void
    {
        $this->answerStruct = $question->getCorrectAnswerStructure()
            ->groupBy(fn($answer) => $answer->correct_answer_id ?? $answer->id);
    }
}
<?php

namespace tcCore\View\Components\Answer\Teacher;

use tcCore\Question;

class RankingQuestion extends QuestionComponent
{
    public $answerStruct;

    protected function setAnswerStruct($question):void
    {
        $this->answerStruct = $question->getCorrectAnswerStructure();
    }
}
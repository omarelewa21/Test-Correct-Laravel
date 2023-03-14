<?php

namespace tcCore\View\Components\Answer\Teacher;

use tcCore\Answer;
use tcCore\Question;


class OpenQuestion extends QuestionComponent
{
    public string $answerValue;

    public function __construct(
        public Question $question,
        public string $editorId,
    )
    {
        parent::__construct($question);
    }

    protected function setAnswerStruct($question): void
    {
        $this->answerValue = $this->question->answer;
    }
}
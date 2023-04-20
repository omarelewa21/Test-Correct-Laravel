<?php

namespace tcCore\View\Components\Answer\Teacher;

use tcCore\Answer;
use tcCore\Question;


class OpenQuestion extends QuestionComponent
{
    public string $answerValue;
    public bool $allowWsc = false;

    public function __construct(
        public Question $question,
        public string $editorId,
    )
    {
        parent::__construct($question);
        $this->allowWsc = auth()->user()->schoolLocation->allow_wsc;
    }

    protected function setAnswerStruct($question): void
    {
        $this->answerValue = $this->question->answer;
    }
}
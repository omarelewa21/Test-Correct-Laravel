<?php

namespace tcCore\View\Components\Answer\Student;

use tcCore\Question;
use tcCore\Answer;

class OpenQuestion extends QuestionComponent
{
    public string $answerValue;
    public bool $allowWsc = false;

    public function __construct(
        public Question $question,
        public Answer   $answer,
        public string $editorId,
    )
    {
        parent::__construct($question, $answer);
        $this->allowWsc = auth()->user()->schoolLocation->allow_wsc;
    }

    protected function setAnswerStruct($question, $answer): void
    {
        $this->answerValue = json_decode($this->answer->json)->value ?? '';
    }
}
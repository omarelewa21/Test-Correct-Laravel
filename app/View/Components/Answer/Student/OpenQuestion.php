<?php

namespace tcCore\View\Components\Answer\Student;

use tcCore\Question;
use tcCore\Answer;

class OpenQuestion extends QuestionComponent
{
    public string $answerValue;

    public function __construct(
        public Question $question,
        public Answer   $answer,
        public string $editorId,
        public bool $webSpellChecker = false,
    )
    {
        parent::__construct($question, $answer);
    }

    protected function setAnswerStruct($question, $answer): void
    {
        $this->answerValue = json_decode($this->answer->json)->value ?? '';
    }
}
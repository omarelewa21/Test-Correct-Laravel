<?php

namespace tcCore\View\Components\Answer\Teacher;

use tcCore\Answer;
use tcCore\Question;

class OpenQuestion extends QuestionComponent
{
    public string $editorId;
    public string $answerValue;

    public function __construct(
        public Question $question,
        public Answer $answer,
    ) {
        parent::__construct($question, $answer);
        $this->editorId = $this->question->uuid . $this->answer->uuid;
        $this->answerValue = json_decode($this->answer->json)->value ?? '';
    }
    public function render()
    {
        return view('components.answer.teacher.open-question');
    }
}
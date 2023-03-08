<?php

namespace tcCore\View\Components\Answer\Student;

use Illuminate\Support\Str;
use Illuminate\View\Component;
use tcCore\Answer;
use tcCore\Question;

abstract class QuestionComponent extends Component
{
    public bool $studentAnswer = true;

    public function __construct(
        public Question $question,
        public Answer $answer,
    ) {
        $this->setAnswerStruct($question, $answer);
    }

    public function render()
    {
        $templateName = Str::kebab(class_basename(get_called_class()));
        return view("components.answer.teacher.$templateName");
    }

    abstract protected function setAnswerStruct($question, $answer): void;
}
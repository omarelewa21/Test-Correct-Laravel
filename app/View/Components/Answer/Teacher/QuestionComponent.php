<?php

namespace tcCore\View\Components\Answer\Teacher;

use Illuminate\Support\Str;
use Illuminate\View\Component;
use tcCore\Question;

abstract class QuestionComponent extends Component
{
    public bool $studentAnswer = false;
    public function __construct(
        public Question $question,
    )
    {
        $this->setAnswerStruct($question);
    }

    public function render()
    {
        $templateName = Str::kebab(class_basename(get_called_class()));
        return view("components.answer.teacher.$templateName");
    }

    abstract protected function setAnswerStruct($question): void;
}
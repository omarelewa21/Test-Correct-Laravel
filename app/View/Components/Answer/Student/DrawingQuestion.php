<?php

namespace tcCore\View\Components\Answer\Student;

class DrawingQuestion extends QuestionComponent
{
    public string $imageSource;
    protected function setAnswerStruct($question, $answer): void
    {
        $prefix = auth()->user()->isA('Student') ? 'student' : 'teacher';
        $this->imageSource = route($prefix.'.drawing-question-answer', $answer->uuid);
    }
}
<?php

namespace tcCore\View\Components\Answer\Student;

class DrawingQuestion extends QuestionComponent
{
    public string $imageSource;
    protected function setAnswerStruct($question, $answer): void
    {
        $this->imageSource = route('teacher.drawing-question-answer', $answer->uuid);
    }
}
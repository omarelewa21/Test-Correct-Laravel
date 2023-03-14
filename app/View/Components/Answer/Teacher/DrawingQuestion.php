<?php

namespace tcCore\View\Components\Answer\Teacher;



class DrawingQuestion extends QuestionComponent
{
    public string $imageSource;
    protected function setAnswerStruct($question): void
    {
        $this->imageSource = route('teacher.drawing-question-answer-model', $question->uuid);
    }
}
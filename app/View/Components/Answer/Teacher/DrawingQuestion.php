<?php

namespace tcCore\View\Components\Answer\Teacher;



class DrawingQuestion extends QuestionComponent
{
    public string $imageSource;
    protected function setAnswerStruct($question): void
    {
        $prefix = auth()->user()->isA('Student') ? 'student' : 'teacher';
        $this->imageSource = route($prefix.'.drawing-question-answer-model', $question->uuid);
    }
}
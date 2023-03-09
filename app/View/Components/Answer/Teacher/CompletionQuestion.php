<?php

namespace tcCore\View\Components\Answer\Teacher;

use tcCore\Http\Traits\Questions\WithCompletionConversion;

class CompletionQuestion extends QuestionComponent
{
    use WithCompletionConversion;

    public mixed $questionTextPartials = [];
    public mixed $questionTextPartialFinal = [];
    public mixed $answerStruct = [];

    protected function setAnswerStruct($question): void
    {
        $this->answerStruct = $question->getCorrectAnswerStructure()->pluck('answer')->map(fn ($answer) => ['given' => $answer]);

        $this->questionTextPartials = $this->explodeAndModifyQuestionText($question->converted_question_html);

        $this->questionTextPartialFinal = $this->questionTextPartials->pop();
    }
}
<?php

namespace tcCore\View\Components\Answer\Teacher;

use tcCore\Http\Traits\Questions\WithCompletionConversion;

class CompletionQuestion extends QuestionComponent
{
    use WithCompletionConversion;

    public mixed $questionTextPartials = [];
    public mixed $questionTextPartialFinal = [];
    public $answerStruct;

    protected function setAnswerStruct($question): void
    {
        $this->answerStruct = $question->getCorrectAnswerStructure()
            ->map(function ($answer) {
                $answer->answerText = $answer->answer;
                return $answer;
            })
            ->where('correct', 1)
            ->values();

        $this->questionTextPartials = $this->explodeAndModifyQuestionText($question->converted_question_html);

        $this->questionTextPartialFinal = $this->questionTextPartials->pop();
    }
}
<?php

namespace tcCore\View\Components\Answer\Student;

use tcCore\Http\Traits\Questions\WithCompletionConversion;

class CompletionQuestion extends QuestionComponent
{
    use WithCompletionConversion;

    public mixed $questionTextPartials = [];
    public mixed $questionTextPartialFinal = [];
    public array $answerStruct = [];

    protected function setAnswerStruct($question, $answer): void
    {
        $correctAnswers = $question->getCorrectAnswerStructure()->pluck('answer');
        $givenAnswers = collect(array_values(json_decode($answer->json, true)))
            ->map(function ($answerOption, $key) use ($correctAnswers){
            return ['given' => $answerOption, 'correct' => $answerOption === $correctAnswers[$key]];
        })->toArray();

        $this->answerStruct = $givenAnswers;

        $this->questionTextPartials = $this->explodeAndModifyQuestionText($question->converted_question_html);

        $this->questionTextPartialFinal = $this->questionTextPartials->pop();
    }
}
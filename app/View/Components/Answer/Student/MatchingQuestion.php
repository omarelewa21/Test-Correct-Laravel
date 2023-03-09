<?php

namespace tcCore\View\Components\Answer\Student;

class MatchingQuestion extends QuestionComponent
{
    public $answerStruct = [];
    public $unusedAnswers = [];

    protected function setAnswerStruct($question, $answer): void
    {
        $givenAnswers = json_decode($answer->json, true);
        $answerOptions = $question->getCorrectAnswerStructure();

        $pairs = $answerOptions->map(function ($answer) use ($answerOptions, $givenAnswers) {
            $answer->pair = $answer->type === 'LEFT'
                ? $answer->order
                : $answerOptions->where('id', $givenAnswers[$answer->id])->first()?->order;

            if (blank($answer->pair)) {
                $answer->pair = 'unused';
            }

            return $answer;
        })
            ->groupBy('pair');

        $this->unusedAnswers = $pairs->get('unused', []);

        $this->answerStruct = $pairs->forget('unused')
            ->map(fn($pair) => $pair->sortBy(['type']))
            ->filter()
            ->sortKeys();
    }
}
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
            $order = $this->getOrderForGivenAnswer($givenAnswers, $answer, $answerOptions);
            $answer->pair = $answer->type === 'LEFT' ? $answer->order : $order;
            $answer->pair ??= 'unused';
            $answer->score = $this->getToggleScore($answerOptions);
            return $answer;
        })->groupBy('pair');

        $this->unusedAnswers = $pairs->get('unused', []);

        $this->answerStruct = $pairs->forget('unused')
            ->map(fn($pair) => $pair->sortBy(['type']))
            ->filter()
            ->sortKeysDesc();
    }

    /**
     * @param mixed $givenAnswers
     * @param $answer
     * @param $answerOptions
     * @return string|null
     */
    private function getOrderForGivenAnswer(mixed $givenAnswers, $answer, $answerOptions): ?string
    {
        return $givenAnswers && isset($givenAnswers[$answer->id])
            ? $answerOptions->where('id', $givenAnswers[$answer->id])->first()?->order
            : 'unused';
    }

    /**
     * @param $answerOptions
     * @return float
     */
    function getToggleScore($answerOptions): float
    {
        return round(
            ($this->question->score / $answerOptions->whereNotNull('correct_answer_id')->count()),
            2
        );
    }
}
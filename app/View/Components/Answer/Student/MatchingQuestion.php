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
        $rating = $this->getTeacherRatingWithToggleData();
        $pairs = $answerOptions->map(function ($answer) use ($answerOptions, $givenAnswers) {
            $order = $this->getOrderForGivenAnswer($givenAnswers, $answer, $answerOptions);
            $answer->pair = $answer->type === 'LEFT' ? $answer->order : $order;
            $answer->pair ??= 'unused';
            $answer->score = $this->getToggleScore($answerOptions);
            return $answer;
        })
            ->groupBy('pair')
            ->each(function ($pair) use ($rating) {
                $correctAnswer = $pair->whereNull('correct_answer_id')->first();
                $pair->whereNotNull('correct_answer_id')
                    ->each(function ($answerOption) use ($correctAnswer, $rating) {
                        $answerOption->activeToggle = $this->getToggleStatus($answerOption, $correctAnswer, $rating);
                    });
            });

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
    private function getToggleScore($answerOptions): float
    {
        return round(
            ($this->question->score / $answerOptions->whereNotNull('correct_answer_id')->count()),
            2
        );
    }

    private function getToggleStatus($answer, $correctAnswer, $rating)
    {
        if (!isset($rating->json[$answer->id])) {
            return $answer->correct_answer_id === $correctAnswer->id;
        }

        if (is_bool($rating->json[$answer->id])) {
            return $rating->json[$answer->id];
        }

        return null;
    }
}
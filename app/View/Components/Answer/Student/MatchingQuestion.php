<?php

namespace tcCore\View\Components\Answer\Student;

use Illuminate\Support\Collection;
use tcCore\Answer;
use tcCore\AnswerRating;
use tcCore\MatchingQuestionAnswer;
use tcCore\Question;

class MatchingQuestion extends QuestionComponent
{
    public $answerStruct = [];
    public $unusedAnswers = [];

    public function __construct(
        public Question $question,
        public Answer   $answer,
        public bool     $disabledToggle = false,
        public bool          $inCoLearning = false,
        public ?AnswerRating $answerRating = null,
    ) {
        parent::__construct($question, $answer);
    }

    protected function setAnswerStruct($question, $answer): void
    {
        $givenAnswers = json_decode($answer->json, true);
        $answerOptions = $question->getCorrectAnswerStructure();

        $pairs = $this->getAnswerOptionPairs($answerOptions, $givenAnswers);

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

    private function getToggleStatus(MatchingQuestionAnswer $answer, MatchingQuestionAnswer $correctAnswer, $rating, bool $inCoLearning): ?bool
    {
        if (isset($rating->json[$answer->id]) && is_bool($rating->json[$answer->id])) {
            return $rating->json[$answer->id];
        }

        if (!isset($rating->json[$answer->id]) && !$inCoLearning) {
            return $answer->correct_answer_id === $correctAnswer->id;
        }

        return null;
    }

    private function addToggleStatusToAnswerOptions($pairs)
    {
        $answerRating = $this->inCoLearning ? $this->answerRating : $this->getTeacherRatingWithToggleData();

        $pairs->where(fn($items, $key) => $key !== 'unused')
            ->each(function ($pair) use ($answerRating) {
                $correctAnswer = $pair->whereNull('correct_answer_id')->first();
                $pair->whereNotNull('correct_answer_id')
                    ->each(function ($answerOption) use ($correctAnswer, $answerRating) {
                        $answerOption->activeToggle = $this->getToggleStatus($answerOption, $correctAnswer, $answerRating, $this->inCoLearning);
                    });
            });

        return $pairs;
    }

    /**
     * @param $answerOptions
     * @param mixed $givenAnswers
     * @return mixed
     */
    private function getAnswerOptionPairs($answerOptions, mixed $givenAnswers): Collection
    {
        $pairs = $answerOptions->map(function ($answer) use ($answerOptions, $givenAnswers) {
            $order = $this->getOrderForGivenAnswer($givenAnswers, $answer, $answerOptions);
            $answer->pair = $answer->type === 'LEFT' ? $answer->order : $order;
            $answer->pair ??= 'unused';
            $answer->score = $this->getToggleScore($answerOptions);
            return $answer;
        })->groupBy('pair');

        return $this->addToggleStatusToAnswerOptions($pairs);
    }
}
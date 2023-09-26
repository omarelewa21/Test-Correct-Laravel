<?php

namespace tcCore\View\Components\Answer\Student;

use tcCore\Answer;
use tcCore\AnswerRating;
use tcCore\Question;

class MultipleChoiceQuestion extends QuestionComponent
{
    public mixed $answerStruct;
    public array $arqStructure = [];
    public ?bool $allOrNothingToggleActive = false;
    public ?bool $trueFalseToggleActive = null;

    public function __construct(
        public Question $question,
        public Answer   $answer,
        public bool     $disabledToggle = false,
        public bool     $inCoLearning = false,
        public ?AnswerRating $answerRating = null,
    ) {
        parent::__construct($question, $answer);
    }

    protected function setAnswerStruct($question, $answer): void
    {
        $givenAnswerIds = collect(json_decode($answer->json))
            ->filter()
            ->keys();

        if ($question->isSubType('ARQ')) {
            $this->arqStructure = \tcCore\MultipleChoiceQuestion::getArqStructure();
        }
        $rating = $this->inCoLearning ? $this->answerRating : $this->getTeacherRatingWithToggleData();

        $this->answerStruct = $question->getCorrectAnswerStructure()
            ->map(function ($link) use ($givenAnswerIds, $rating) {
                $link->active = $givenAnswerIds->contains($link->multiple_choice_question_answer_id);
                $link->toggleStatus = $this->getToggleStatus($link, $rating);
                return $link;
            });

        if ($question->all_or_nothing) {
            $correctIds = $this->answerStruct->filter(fn($link) => $link->score > 0)
                ->map(fn($link) => $link->multiple_choice_question_answer_id);

            $this->allOrNothingToggleActive = $this->getAllOrNothingToggleActive($correctIds, $givenAnswerIds, $rating);
        }
    }

    /**
     * @param $givenAnswerIds
     * @param $link
     * @return mixed
     */
    private function getToggleStatus($link, $rating): ?bool
    {
        if ($this->question->isSubType('TrueFalse')) {
            return $this->setTrueFalseToggleStatus($rating);
        }

        if (isset($rating->json[$link->order]) && is_bool($rating->json[$link->order])) {
            return $rating->json[$link->order];
        }

        if($this->inCoLearning) {
            return null;
        }

        return $link->active && $link->score > 0;
    }

    private function getAllOrNothingToggleActive($correctIds, $givenAnswerIds, $rating)
    {
        if ($correctIds->count() !== $givenAnswerIds->count() && !$this->inCoLearning) {
            return false;
        }
        if ($this->ratingHasBoolValueForKey($rating, $this->question->id)) {
            return $rating->json[$this->question->id];
        }
        if($this->inCoLearning) {
            return null;
        }
        return $correctIds->diff($givenAnswerIds)->isEmpty();
    }

    private function setTrueFalseToggleStatus(?AnswerRating $teacherOrStudentRating): ?bool
    {
        //colearning needs to default to null, assessment defaults to 'rating === score'
        $rating = $teacherOrStudentRating ?? null;
        if(!$this->inCoLearning) {
            $rating ??= $this->answer->answerRatings()->where('type', AnswerRating::TYPE_SYSTEM)->first();
        }
        $this->trueFalseToggleActive = $this->ratingHasBoolValueForKey($rating, $this->question->id)
            ? $rating->json[$this->question->id]
            : ($this->inCoLearning ? null : (float)$rating?->rating === (float)$this->question->score);
        return $this->trueFalseToggleActive;
    }
}
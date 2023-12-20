<?php

namespace tcCore\View\Components\Answer\Student;

use Illuminate\Support\Str;
use Illuminate\View\Component;
use tcCore\Answer;
use tcCore\AnswerRating;
use tcCore\Question;

abstract class QuestionComponent extends Component
{
    public bool $studentAnswer = true;
    public bool $enableComments = false;

    public function __construct(
        public Question $question,
        public Answer $answer,
    ) {
        $this->setAnswerStruct($question, $answer);
    }

    public function render()
    {
        $templateName = Str::kebab(class_basename(get_called_class()));
        return view("components.answer.teacher.$templateName");
    }

    abstract protected function setAnswerStruct($question, $answer): void;


    /**
     * @return AnswerRating|null
     */
    protected function getTeacherRatingWithToggleData(): ?AnswerRating
    {
        if (!$this->studentAnswer) return null;
        return $this->answer->teacherRatings()->whereNotNull('json')->first();
    }

    /**
     * @param $rating
     * @return bool
     */
    protected function ratingHasBoolValueForKey($rating, $id): bool
    {
        if (!$rating) return false;
        return isset($rating->json[$id]) && is_bool($rating->json[$id]);
    }

    /**
     * @param $rating
     * @return bool
     */
    protected function ratingContainsValidToggleValue($rating, $id): bool
    {
        if (!$rating) return false;
        return isset($rating->json[$id]) && in_array((float)$rating->json[$id], [0.0, 1.0, 0.5]);
    }
}
<?php

namespace tcCore\View\Components\Answer\Student;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use tcCore\Answer;
use tcCore\AnswerRating;
use tcCore\Http\Traits\Questions\WithCompletionConversion;
use tcCore\Question;

class CompletionQuestion extends QuestionComponent
{
    use WithCompletionConversion;

    public mixed $questionTextPartials = [];
    public mixed $questionTextPartialFinal = [];
    public $answerStruct;

    public function __construct(
        public Question      $question,
        public Answer        $answer,
        public bool          $disabledToggle = false,
        public bool          $showToggles = true,
        public bool          $inAssessment = false,
        public bool          $inCoLearning = false,
        public ?AnswerRating $answerRating = null,
    ) {
        parent::__construct($question, $answer);
    }

    protected function setAnswerStruct($question, $answer): void
    {
        $correctAnswers = $question->getCorrectAnswerStructure();
        $givenAnswers = array_values(json_decode($answer->json ?? '{}', true));
        $answers = $this->matchGivenAnswersWithRequiredAmount($correctAnswers, $givenAnswers);

        $this->answerStruct = $question->isSubType('completion')
            ? $this->createCompletionAnswerStruct($answers, $correctAnswers, $answer)
            : $this->createSelectionAnswerStruct($answers, $correctAnswers);

        $this->questionTextPartials = $this->explodeAndModifyQuestionText($question->converted_question_html);

        $this->questionTextPartialFinal = $this->questionTextPartials->pop();
    }


    /**
     * @param $givenAnswer
     * @param $correctAnswer
     * @return bool|null
     */
    private function isToggleActiveForAnswer($givenAnswer, $correctAnswer): ?bool
    {
        if( $this->inCoLearning ) {
            if ($studentAnswerRating = $this->answerRating) {
                if ($this->ratingHasBoolValueForKey($studentAnswerRating, $correctAnswer->tag)) {
                    return $studentAnswerRating->json[$correctAnswer->tag];
                }
            }
            return null;
        }

        if ($this->question->isSubType('multi')) {
            return $givenAnswer === $correctAnswer->answer;
        }

        if (!$this->answer->answerRatings) {
            return null;
        }

        if ($teacherRating = $this->getTeacherRatingWithToggleData()) {
            if ($this->ratingHasBoolValueForKey($teacherRating, $correctAnswer->tag)) {
                return $teacherRating->json[$correctAnswer->tag];
            }
        }

        if ($this->question->isSubType('completion') && !$this->question->auto_check_answer) {
            return null;
        }

        if ($this->question->auto_check_answer_case_sensitive) {
            return in_array($givenAnswer, Arr::wrap($correctAnswer->answer)) ?: null;
        }

        $lowercaseAnswers = collect(Arr::wrap($correctAnswer->answer))
            ->map(fn($answer) => Str::lower($answer))
            ->toArray();

        return in_array(Str::lower($givenAnswer), $lowercaseAnswers) ?: null;
    }

    private function createCompletionAnswerStruct(mixed $answers, $correctAnswers, $answer)
    {
        $answerStruct = $correctAnswers->unique('tag')->values()->map(fn($answerOption) => (object)$answerOption);

        $correctAnswers->each(function ($correctAnswer, $key) use (&$answerStruct) {
            $answerOption = $answerStruct->where('tag', $correctAnswer->tag)->first();

            $answerOption->answer = array_unique(
                array_merge(Arr::wrap($answerOption->answer), Arr::wrap($correctAnswer->answer))
            );
        });

        $score = $this->question->score / $correctAnswers->where('correct', 1)
                                                         ->unique('tag')
                                                         ->count();

        return $answerStruct->map(function ($link, $key) use ($answer, $answers, $score) {
            return $this->setAnswerPropertiesOnObject($link, $key, $link, $answers, $score);
        });
    }

    private function createSelectionAnswerStruct(mixed $answers, $correctAnswers)
    {
        return collect($answers)->map(function ($link, $key) use ($answers, $correctAnswers) {
            $answer = (object)$link;
            $correctAnswer = $correctAnswers->where('correct', 1)->values()->get($key);
            $score = $this->question->score / $correctAnswers->where('correct', 1)->count();
            return $this->setAnswerPropertiesOnObject($answer, $key, $correctAnswer, $answers, $score);
        })->values();
    }

    private function setAnswerPropertiesOnObject($object, $key, $correctAnswer, $answers, $score)
    {
        $hasValue = isset($answers[$key]) && filled($answers[$key]);
        $object->answerText = $hasValue ? $answers[$key] : '......';
        $object->answered = $hasValue;
        $object->activeToggle = $hasValue ? $this->isToggleActiveForAnswer(
            $answers[$key],
            $correctAnswer
        ) : null;
        $object->score = $score;
        $object->tag = $hasValue ? $correctAnswer->tag : '';
        return $object;
    }

    /**
     * @param $correctAnswers
     * @param mixed $answers
     * @return mixed
     */
    private function matchGivenAnswersWithRequiredAmount($correctAnswers, array $answers): array
    {
        return $correctAnswers->filter(fn($answer) => $answer->correct)
                                         ->unique('tag')->map(fn($answer) => isset($answers[$answer->tag - 1]) ? $answers[$answer->tag - 1] : '')
                                         ->values()->toArray();
    }

    public function render()
    {
        if (!($this->inAssessment || $this->inCoLearning)) {
            return view("components.answer.student.completion-question");
        }

        return parent::render();
    }
}
<?php

namespace tcCore;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use tcCore\Http\Requests\UpdateTestQuestionRequest;
use tcCore\Lib\Question\QuestionInterface;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use Ramsey\Uuid\Uuid;
use tcCore\Traits\UuidTrait;

class MultipleChoiceQuestion extends Question implements QuestionInterface
{

    use UuidTrait;

    protected $casts = [
        'uuid'       => EfficientUuid::class,
        'deleted_at' => 'datetime',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'multiple_choice_questions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['subtype', 'selectable_answers'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function question()
    {
        return $this->belongsTo('tcCore\Question', $this->getKeyName());
    }

    public function multipleChoiceQuestionAnswerLinks()
    {
        return $this->hasMany('tcCore\MultipleChoiceQuestionAnswerLink', 'multiple_choice_question_id');
    }

    public function multipleChoiceQuestionAnswers()
    {
        return $this->belongsToMany(
            'tcCore\MultipleChoiceQuestionAnswer',
            'multiple_choice_question_answer_links',
            'multiple_choice_question_id',
            'multiple_choice_question_answer_id'
        )->withPivot(
            [
                $this->getCreatedAtColumn(),
                $this->getUpdatedAtColumn(),
                'order'
            ]
        )->wherePivot(
            $this->getDeletedAtColumn(), null
        )->orderBy(
            'multiple_choice_question_answer_links.order'
        );
    }

    public function reorder(MultipleChoiceQuestionAnswerLink $movedAnswer)
    {
        $answers = $this->multipleChoiceQuestionAnswerLinks()->orderBy('order')->get();

        $this->performReorder($answers, $movedAnswer, 'order');
    }

    public function loadRelated()
    {
        $this->load('multipleChoiceQuestionAnswers');
    }

    public function duplicate(array $attributes, $ignore = null)
    {
        $question = $this->replicate();
        $question->parentInstance = $this->parentInstance->duplicate($attributes, $ignore);
        if ($question->parentInstance === false) {
            return false;
        }

        $question->fill($attributes);

        $question->setAttribute('uuid', Uuid::uuid4());

        if ($question->save() === false) {
            return false;
        }

        foreach ($this->multipleChoiceQuestionAnswerLinks as $multipleChoiceQuestionAnswerLink) {
            if ($ignore instanceof MultipleChoiceQuestionAnswer && $ignore->getKey() == $multipleChoiceQuestionAnswerLink->getAttribute('multiple_choice_question_answer_id')) {
                continue;
            }

            if ($ignore instanceof MultipleChoiceQuestionAnswerLink
                && $ignore->getAttribute('multiple_choice_question_answer_id') == $multipleChoiceQuestionAnswerLink->getAttribute('multiple_choice_question_answer_id')
                && $ignore->getAttribute('multiple_choice_question_id') == $multipleChoiceQuestionAnswerLink->getAttribute('multiple_choice_question_id')) {
                continue;
            }

            if ($multipleChoiceQuestionAnswerLink->duplicate($question, []) === false) {
                return false;
            }
        }

        return $question;
    }

    public function checkAnswer($answer)
    {
        $multipleChoiceQuestionAnswers = $this->multipleChoiceQuestionAnswers;

        $answers = json_decode($answer->getAttribute('json'), true);
        if (!$answers) {
            return 0;
        }

        $score = 0;
        $maxScore = 0;
        $countCorrectAnswers = 0;

        $givenAnswers = 0;
        foreach ($answers as $key => $val) {
            if ($val == 1) $givenAnswers++;
        }

        foreach ($multipleChoiceQuestionAnswers as $multipleChoiceQuestionAnswer) {
            $questionAnswerScore = $multipleChoiceQuestionAnswer->getAttribute('score');
            if ($questionAnswerScore > 0) {
                $countCorrectAnswers++;
                $maxScore += $questionAnswerScore;
            }
            if (array_key_exists($multipleChoiceQuestionAnswer->getKey(), $answers) && $answers[$multipleChoiceQuestionAnswer->getKey()] == 1) {
                $score += $questionAnswerScore;
            }
        }


        if ($this->allOrNothingQuestion()) {
            if (Str::lower($this->subtype) == 'arq') {
                if ($score == $this->score) {
                    return $score;
                }
            } else if ($score == $maxScore && $countCorrectAnswers === $givenAnswers) {
                return $this->score;
            }
            return 0;
        }

        if ($score > $this->getAttribute('score')) {
            $score = $this->getAttribute('score');
        }

        if ($this->getAttribute('decimal_score') == true) {
            $score = floor($score * 2) / 2;
        } else {
            $score = floor($score);
        }


        return $score;
    }

    public function isClosedQuestion(): bool
    {
        return true;
    }

    public function deleteAnswers()
    {
        $this->multipleChoiceQuestionAnswerLinks->each(function ($qAL) {
            if (!$qAL->delete()) {
                throw new QuestionException('Failed to delete multiple choice question answer link', 422);
            }
            if ($qAL->multipleChoiceQuestionAnswer->isUsed($qAL)) {
                // all okay, this one should be kept
            } else {
                if (!$qAL->multipleChoiceQuestionAnswer->delete()) {
                    throw new QuestionException('Failed to delete multiple choice question answer', 422);
                }
            }
        });
        return true;
    }

    public function addAnswers($mainQuestion, $answers)
    {

        $question = $this;
        foreach ($answers as $answerDetails) {
            if (!$this->isValidAnswerDetails($answerDetails)) {
                continue;
            }
            $multipleChoiceQuestionAnswer = new MultipleChoiceQuestionAnswer();

            $multipleChoiceQuestionAnswer->fill($answerDetails);
            if (!$multipleChoiceQuestionAnswer->save()) {
                throw new QuestionException('Failed to create multiple choice question answer', 422);
            }

            $multipleChoiceQuestionAnswersLink = new MultipleChoiceQuestionAnswerLink();
            // important!!!
            $multipleChoiceQuestionAnswersLink->fill($answerDetails);
            $multipleChoiceQuestionAnswersLink->setAttribute('multiple_choice_question_id', $question->getKey());
            $multipleChoiceQuestionAnswersLink->setAttribute('multiple_choice_question_answer_id', $multipleChoiceQuestionAnswer->getKey());

            if (!$multipleChoiceQuestionAnswersLink->save()) {
                throw new QuestionException('Failed to create multiple choice question answer', 422);
            }

        }
        return true;
    }

    private function isValidAnswerDetails($answer)
    {
        if (!array_key_exists('answer', $answer) && !array_key_exists('score', $answer)) {
            return false;
        }
        if (!array_key_exists('answer', $answer) && $answer['score'] != '') {
            return true;
        }
        if (array_key_exists('answer', $answer)) {
            if ($answer['answer'] == '' && !array_key_exists('score', $answer)) {
                return false;
            }
            if ($answer['answer'] == '' && $answer['score'] == '') {
                return false;
            }
        }
        if (array_key_exists('answer', $answer) && $this->subtype != 'ARQ') {
            if ($answer['answer'] == '' && $answer['score'] == '0') {
                return false;
            }
        }
        return true;
    }

    public function getCaptionAttribute()
    {
        if ($this->subtype === 'TrueFalse') {
            return __('test_take.multiple_choice_question_true_false');
        }

        if ($this->subtype === 'ARQ') {
            return __('test_take.multiple_choice_question_arq');
        }

        return parent::getCaptionAttribute();

    }

    public function needsToBeUpdated($request)
    {
        $totalData = $this->getTotalDataForTestQuestionUpdate($request);
        if ($this->isDirtyAnswerOptions($totalData)) {
            return true;
        }
        return parent::needsToBeUpdated($request);
    }

    public static function getArqStructure()
    {
        return [
            ['A', 'test_take.correct', 'test_take.correct', 'test_take.correct_reason'],
            ['B', 'test_take.correct', 'test_take.correct', 'test_take.incorrect_reason'],
            ['C', 'test_take.correct', 'test_take.incorrect', 'test_take.not_applicable'],
            ['D', 'test_take.incorrect', 'test_take.correct', 'test_take.not_applicable'],
            ['E', 'test_take.incorrect', 'test_take.incorrect', 'test_take.not_applicable'],
        ];
    }

    public function getCorrectAnswerStructure()
    {
        return MultipleChoiceQuestionAnswerLink::join('multiple_choice_question_answers', 'multiple_choice_question_answers.id', '=', 'multiple_choice_question_answer_links.multiple_choice_question_answer_id')
            ->select(['multiple_choice_question_answer_links.*', 'multiple_choice_question_answers.answer', 'multiple_choice_question_answers.score'])
            ->orderBy('multiple_choice_question_answer_links.order', 'asc')
            ->where('multiple_choice_question_id', $this->getKey())
            ->whereNull('multiple_choice_question_answers.deleted_at')
            ->get();
    }

    public function isFullyAnswered(Answer $answer): bool
    {
        $givenAnswersCount = collect(json_decode($answer->json, true))->filter()->count();
        return $givenAnswersCount === $this->selectable_answers;
    }
}

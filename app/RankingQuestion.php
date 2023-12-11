<?php namespace tcCore;

use Illuminate\Support\Facades\Log;
use tcCore\Exceptions\QuestionException;
use tcCore\Http\Requests\UpdateTestQuestionRequest;
use tcCore\Lib\Question\QuestionInterface;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use Ramsey\Uuid\Uuid;
use tcCore\Traits\UuidTrait;

class RankingQuestion extends Question implements QuestionInterface
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
    protected $table = 'ranking_questions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['random_order'];

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

    public function rankingQuestionAnswerLinks()
    {
        return $this->hasMany('tcCore\RankingQuestionAnswerLink', 'ranking_question_id');
    }

    public function rankingQuestionAnswers()
    {
        return $this->belongsToMany('tcCore\RankingQuestionAnswer', 'ranking_question_answer_links', 'ranking_question_id', 'ranking_question_answer_id')->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn(), 'order', 'correct_order'])->wherePivot($this->getDeletedAtColumn(), null)->orderBy('ranking_question_answer_links.order');
    }

    public function reorder(RankingQuestionAnswerLink $movedAnswer, $attribute)
    {
        $answers = $this->rankingQuestionAnswerLinks()->orderBy($attribute)->get();

        $this->performReorder($answers, $movedAnswer, $attribute);
    }

    public function loadRelated()
    {
        $this->load('rankingQuestionAnswers');
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

        foreach ($this->rankingQuestionAnswerLinks as $rankingQuestionAnswerLink) {
            if ($ignore instanceof RankingQuestionAnswer && $ignore->getKey() == $rankingQuestionAnswerLink->getAttribute('ranking_question_answer_id')) {
                continue;
            }

            if ($ignore instanceof RankingQuestionAnswerLink
                && $ignore->getAttribute('ranking_question_answer_id') == $rankingQuestionAnswerLink->getAttribute('ranking_question_answer_id')
                && $ignore->getAttribute('ranking_question_id') == $rankingQuestionAnswerLink->getAttribute('ranking_question_id')) {
                continue;
            }

            if ($rankingQuestionAnswerLink->duplicate($question, []) === false) {
                return false;
            }
        }

        return $question;
    }

    public function deleteAnswers()
    {
        $this->rankingQuestionAnswerLinks->each(function ($qAL) {
            if (!$qAL->delete()) {
                throw new QuestionException('Failed to delete ranking question answer link', 422);
            }

            if ($qAL->rankingQuestionAnswer->isUsed($qAL)) {
                // all okay, this one should be kept
            } else {
                if (!$qAL->rankingQuestionAnswer->delete()) {
                    throw new QuestionException('Failed to delete ranking question answer', 422);
                }
            }
        });
        return true;
    }

    public function addAnswers($mainQuestion, $answers)
    {
        $question = $this;
        foreach ($answers as $answerDetails) {
            if ($answerDetails['answer'] != '') {

                $rankingQuestionAnswer = new RankingQuestionAnswer();

                $rankingQuestionAnswer->fill($answerDetails);
                if (!$rankingQuestionAnswer->save()) {
                    throw new QuestionException('Failed to create ranking question answer', 422);
                }

                $rankingQuestionAnswerLink = new RankingQuestionAnswerLink();
                // important!!!
                $rankingQuestionAnswerLink->setPreventReorder(true);
                $rankingQuestionAnswerLink->fill($answerDetails);
                $rankingQuestionAnswerLink->setAttribute('ranking_question_id', $question->getKey());
                $rankingQuestionAnswerLink->setAttribute('ranking_question_answer_id', $rankingQuestionAnswer->getKey());

                if (!$rankingQuestionAnswerLink->save()) {
                    throw new QuestionException('Failed to create ranking question answer', 422);
                }
            }
        }
        return true;
    }

    public function canCheckAnswer($answer)
    {
        return true;
    }

    public function checkAnswer($answer)
    {
        $answers = json_decode($answer->getAttribute('json'), true);
        if (!$answers) {
            return 0;
        }
        asort($answers);

        $prev = null;
        $beforeAndAfterAnswers = [];
        foreach ($answers as $answerId => $order) {
            if ($prev) {
                $beforeAndAfterAnswers[$prev]['before'] = $answerId;
                $beforeAndAfterAnswers[$answerId] = ['after' => $prev, 'before' => null];
            } else {
                $beforeAndAfterAnswers[$answerId] = ['after' => null, 'before' => null];
            }

            $prev = $answerId;
        }

        $rankingQuestionAnswers = $this->RankingQuestionAnswerLinks;

        $orderAnswers = [];
        foreach ($rankingQuestionAnswers as $rankingQuestionAnswer) {
            if (array_key_exists($rankingQuestionAnswer->getAttribute('order'), $orderAnswers)) {
                return false;
            }

            $orderAnswers[$rankingQuestionAnswer->getAttribute('order')] = $rankingQuestionAnswer->getAttribute('ranking_question_answer_id');
        }

        ksort($orderAnswers);
        $beforeAndAfter = [];
        $prev = null;
        foreach ($orderAnswers as $answerId) {
            if ($prev) {
                $beforeAndAfter[$prev]['before'] = $answerId;
                $beforeAndAfter[$answerId] = ['after' => $prev, 'before' => null];
            } else {
                $beforeAndAfter[$answerId] = ['after' => null, 'before' => null];
            }

            $prev = $answerId;
        }

        $correct = 0;
        foreach ($beforeAndAfter as $key => $correctAnswer) {
            if (array_key_exists($key, $beforeAndAfterAnswers) && $correctAnswer['after'] == $beforeAndAfterAnswers[$key]['after'] && $correctAnswer['before'] == $beforeAndAfterAnswers[$key]['before']) {
                $correct++;
            }
        }

        if ($this->allOrNothingQuestion()) {
            if ($correct == count($orderAnswers)) {
                return $this->score;
            } else {
                return 0;
            }
        }


        $score = $this->getAttribute('score') * ($correct / count($orderAnswers));
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

    public function needsToBeUpdated($request)
    {
        $totalData = $this->getTotalDataForTestQuestionUpdate($request);
        if ($this->isDirtyAnswerOptions($totalData)) {
            return true;
        }
        return parent::needsToBeUpdated($request);
    }

    public function getCorrectAnswerStructure()
    {
        return RankingQuestionAnswerLink::join(
            'ranking_question_answers',
            'ranking_question_answers.id',
            '=',
            'ranking_question_answer_links.ranking_question_answer_id'
        )
            ->selectRaw('ranking_question_answer_links.*, ranking_question_answers.answer')
            ->orderBy('ranking_question_answer_links.correct_order', 'asc')
            ->orderBy('ranking_question_answer_links.order', 'asc')
            ->where('ranking_question_id', $this->getKey())
            ->whereNull('ranking_question_answers.deleted_at')
            ->get();
    }
}

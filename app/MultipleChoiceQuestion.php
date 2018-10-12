<?php 

namespace tcCore;

use Illuminate\Support\Facades\Log;
use tcCore\Lib\Question\QuestionInterface;

class MultipleChoiceQuestion extends Question implements QuestionInterface {

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

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

    public function question() {
        return $this->belongsTo('tcCore\Question', $this->getKeyName());
    }

    public function multipleChoiceQuestionAnswerLinks() {
        return $this->hasMany('tcCore\MultipleChoiceQuestionAnswerLink', 'multiple_choice_question_id');
    }

    public function multipleChoiceQuestionAnswers() {
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

    public function reorder(MultipleChoiceQuestionAnswerLink $movedAnswer) {
        $answers = $this->multipleChoiceQuestionAnswerLinks()->orderBy('order')->get();

        $this->performReorder($answers, $movedAnswer, 'order');
    }

    public function loadRelated()
    {
        $this->load('multipleChoiceQuestionAnswers');
    }

    public function duplicate(array $attributes, $ignore = null) {
        $question = $this->replicate();

        $question->parentInstance = $this->parentInstance->duplicate($attributes, $ignore);
        if ($question->parentInstance === false) {
            return false;
        }

        $question->fill($attributes);

        if ($question->save() === false) {
            return false;
        }

        foreach($this->multipleChoiceQuestionAnswerLinks as $multipleChoiceQuestionAnswerLink) {
            if ($ignore instanceof MultipleChoiceQuestionAnswer && $ignore->getKey() == $multipleChoiceQuestionAnswerLink->getAttribute('multiple_choice_question_answer_id')) {
                continue;
            }

            if ($ignore instanceof MultipleChoiceQuestionAnswerLink
                && $ignore->getAttribute('multiple_choice_question_answer_id') == $multipleChoiceQuestionAnswerLink->getAttribute('multiple_choice_question_answer_id')
                && $ignore->getAttribute('multiple_choice_question_id') == $multipleChoiceQuestionAnswerLink->getAttribute('multiple_choice_question_id')) {
                continue;
            }

            if($multipleChoiceQuestionAnswerLink->duplicate($question, []) === false) {
                return false;
            }
        }

        return $question;
    }

    public function canCheckAnswer() {
        return true;
    }

    public function checkAnswer($answer) {
        $multipleChoiceQuestionAnswers = $this->multipleChoiceQuestionAnswers;

        $answers = json_decode($answer->getAttribute('json'), true);
        if(!$answers) {
            return 0;
        }

        $score = 0;
        foreach($multipleChoiceQuestionAnswers as $multipleChoiceQuestionAnswer) {
            if (array_key_exists($multipleChoiceQuestionAnswer->getKey(), $answers) && $answers[$multipleChoiceQuestionAnswer->getKey()] == 1) {
                $score += $multipleChoiceQuestionAnswer->getAttribute('score');
            }
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
}

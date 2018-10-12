<?php namespace tcCore;

use tcCore\Lib\Question\QuestionInterface;

class MatchingQuestion extends Question implements QuestionInterface {

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
    protected $table = 'matching_questions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['subtype'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function question() {
        return $this->belongsTo('tcCore\Question', $this->getKeyName());
    }

    public function matchingQuestionAnswerLinks() {
        return $this->hasMany('tcCore\MatchingQuestionAnswerLink', 'matching_question_id');
    }

    public function matchingQuestionAnswers() {
        return $this->belongsToMany('tcCore\MatchingQuestionAnswer', 'matching_question_answer_links', 'matching_question_id', 'matching_question_answer_id')->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn(), 'order'])->wherePivot($this->getDeletedAtColumn(), null)->orderBy('matching_question_answer_links.order');
    }

    public function reorder(MatchingQuestionAnswerLink $movedAnswer) {
        $answers = $this->matchingQuestionAnswerLinks()->join('matching_question_answers', 'matching_question_answers.id', '=', 'matching_question_answer_links.matching_question_answer_id')->where('matching_question_answers.type', $movedAnswer->matchingQuestion->getAttribute('type'))->orderBy('order')->get(['matching_question_answer_links.*']);

        $this->performReorder($answers, $movedAnswer, 'order');
    }

    public function loadRelated()
    {
        $this->load('matchingQuestionAnswers');
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

        $skipped = [];
        foreach($this->matchingQuestionAnswerLinks as $matchingQuestionAnswerLink) {
            if ($ignore instanceof MatchingQuestionAnswer && $ignore->getKey() == $matchingQuestionAnswerLink->getAttribute('matching_question_answer_id')) {
                $skipped[] = $matchingQuestionAnswerLink->getAttribute('matching_question_answer_id');
            }

            if ($ignore instanceof MatchingQuestionAnswerLink
                && $ignore->getAttribute('matching_question_answer_id') == $matchingQuestionAnswerLink->getAttribute('matching_question_answer_id')
                && $ignore->getAttribute('matching_question_id') == $matchingQuestionAnswerLink->getAttribute('matching_question_id')
            ) {
                $skipped[] = $matchingQuestionAnswerLink->getAttribute('matching_question_answer_id');
            }
        }

        foreach($this->matchingQuestionAnswerLinks as $matchingQuestionAnswerLink) {
            if(in_array($matchingQuestionAnswerLink->getAttribute('matching_question_answer_id'), $skipped) || in_array($matchingQuestionAnswerLink->getAttribute('correct_answer_id'), $skipped)) {
                continue;
            }

            if($matchingQuestionAnswerLink->duplicate($question, []) === false) {
                return false;
            }
        }

        return $question;
    }

    public function canCheckAnswer() {
        return true;
    }

    public function checkAnswer($answer) {
        $matchingQuestionAnswers = $this->matchingQuestionAnswers;

        $possibleAnswers = [];
        foreach($matchingQuestionAnswers as $matchingQuestionAnswer) {
            if ($matchingQuestionAnswer->getAttribute('type') === 'LEFT') {
                $possibleAnswers[] = $matchingQuestionAnswer->getKey();
            }
        }

        $correctAnswers = [];
        foreach($matchingQuestionAnswers as $matchingQuestionAnswer) {
            if ($matchingQuestionAnswer->getAttribute('type') === 'RIGHT' && in_array($matchingQuestionAnswer->getAttribute('correct_answer_id'), $possibleAnswers)) {
                $correctAnswers[$matchingQuestionAnswer->getKey()] = $matchingQuestionAnswer->getAttribute('correct_answer_id');
            }
        }

        $answers = json_decode($answer->getAttribute('json'), true);
        if(!$answers) {
            return 0;
        }

        $correct = 0;
        foreach($correctAnswers as $right => $left) {
            if (array_key_exists($right, $answers) && $answers[$right] == $left) {
                $correct++;
            }
        }

        $score = $this->getAttribute('score') * ($correct / count($correctAnswers));
        if ($this->getAttribute('decimal_score') == true) {
            $score = floor($score * 2) / 2;
        } else {
            $score = floor($score);
        }

        return $score;
    }
}

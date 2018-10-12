<?php namespace tcCore;

use tcCore\Lib\Question\QuestionInterface;

class CompletionQuestion extends Question implements QuestionInterface {

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
    protected $table = 'completion_questions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['rating_method', 'subtype'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function question() {
        return $this->belongsTo('tcCore\Question', $this->getKeyName());
    }

    public function completionQuestionAnswerLinks() {
        return $this->hasMany('tcCore\CompletionQuestionAnswerLink', 'completion_question_id');
    }

    public function completionQuestionAnswers() {
        return $this->belongsToMany(
            'tcCore\CompletionQuestionAnswer',
            'completion_question_answer_links',
            'completion_question_id',
            'completion_question_answer_id'
        )->withPivot(
            [
                $this->getCreatedAtColumn(),
                $this->getUpdatedAtColumn(),
                $this->getDeletedAtColumn()
            ]
        )->wherePivot($this->getDeletedAtColumn(), null);
    }

    public function loadRelated()
    {
        $this->load('completionQuestionAnswers');
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

        foreach($this->completionQuestionAnswerLinks as $completionQuestionAnswerLink) {
            if ($ignore instanceof CompletionQuestionAnswer && $ignore->getKey() == $completionQuestionAnswerLink->getAttribute('completion_question_answer_id')) {
                continue;
            }

            if ($ignore instanceof CompletionQuestionAnswerLink
                && $ignore->getAttribute('completion_question_answer_id') == $completionQuestionAnswerLink->getAttribute('completion_question_answer_id')
                && $ignore->getAttribute('completion_question_id') == $completionQuestionAnswerLink->getAttribute('completion_question_id')) {
                continue;
            }

            if($completionQuestionAnswerLink->duplicate($question, []) === false) {
                return false;
            }
        }

        return $question;
    }

    public function canCheckAnswer() {
        $completionQuestionAnswers = $this->completionQuestionAnswers->groupBy('tag');
        $tags = [];

        if (!$completionQuestionAnswers) {
            return false;
        }

        foreach($completionQuestionAnswers as $tag => $choices) {
            if (count($choices) <= 1) {
                return false;
            }

            $hasCorrect = false;
            foreach ($choices as $choice) {
                if ($choice->getAttribute('correct') == 1) {
                    $hasCorrect = true;
                    break;
                }
            }

            if (!$hasCorrect) {
                return false;
            }
        }

        return true;
    }

    public function checkAnswer($answer) {
        $completionQuestionAnswers = $this->completionQuestionAnswers->groupBy('tag');
        foreach($completionQuestionAnswers as $tag => $choices) {
            $answers = [];
            foreach ($choices as $choice) {
                if ($choice->getAttribute('correct') == 1) {
                    $answers[] = $choice->getAttribute('answer');
                }
            }
            $completionQuestionAnswers[$tag] = $answers;
        }

        $answers = json_decode($answer->getAttribute('json'), true);
        if(!$answers) {
            return 0;
        }

        $correct = 0;
        foreach($completionQuestionAnswers as $tag => $tagAnswers) {
            if (!array_key_exists($tag, $answers)) {
                continue;
            }

            if (in_array($answers[$tag], $tagAnswers)) {
                $correct++;
            }
        }

        $score = $this->getAttribute('score') * ($correct / count($completionQuestionAnswers));
        if ($this->getAttribute('decimal_score') == true) {
            $score = floor($score * 2) / 2;
        } else {
            $score = floor($score);
        }

        return $score;
    }
}

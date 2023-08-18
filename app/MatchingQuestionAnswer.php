<?php namespace tcCore;

use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class MatchingQuestionAnswer extends BaseModel {

    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'matching_question_answers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['correct_answer_id', 'answer', 'type'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function matchingQuestionAnswerLinks() {
        return $this->hasMany('tcCore\MatchingQuestionAnswerLink', 'matching_question_answer_id');
    }

    public function questions() {
        return $this->belongsToMany('tcCore\Question',
            'matching_question_answer_links',
            'matching_question_answer_id',
            'matching_question_id'
        )->withPivot(
            [
                $this->getCreatedAtColumn(),
                $this->getUpdatedAtColumn(),
                // $this->getDeletedAtColumn()
            ]
        )->wherePivot('matching_question_answer_links.deleted_at', null);
        // )->wherePivot($this->getDeletedAtColumn(), null);
    }

    public function matchingChoiceQuestions() {
        return $this->belongsToMany('tcCore\MatchingQuestion',
            'matching_question_answer_links',
            'matching_question_answer_id',
            'matching_question_id'
        )->withPivot(
            [
                $this->getCreatedAtColumn(),
                $this->getUpdatedAtColumn(),
                // $this->getDeletedAtColumn()
            ]
        )->wherePivot('matching_question_answer_links.deleted_at', null);
        // )->wherePivot($this->getDeletedAtColumn(), null);
    }

    public function matchingQuestionAnswer() {
        return $this->hasMany('tcCore\MatchingQuestionAnswer', 'correct_answer_id');
    }

    public function correctAnswer() {
        return $this->matchingQuestionAnswer();
    }

    public function duplicate(MatchingQuestion $matchingQuestion, array $attributes) {
        $matchingQuestionAnswer = $this->replicate();
        $matchingQuestionAnswer->fill($attributes);

        $matchingQuestionAnswer->save();

        return $matchingQuestionAnswer;
    }

    public function isUsed($ignoreRelationTo) {
        $uses = $this->matchingQuestionAnswerLinks()->withTrashed();

        if ($ignoreRelationTo instanceof MatchingQuestion) {
            $ignoreRelationTo->where('matching_question_id', '!=', $ignoreRelationTo->getKey());
        }

        if ($ignoreRelationTo instanceof MatchingQuestionAnswerLink) {
            $ignoreRelationTo->where('matching_question_id', '!=', $ignoreRelationTo->getAttribute('matching_question_id'));
        }

        return $uses->count() > 0;
    }

    public function isGroup(): bool
    {
        return blank($this->correct_answer_id);
    }
}

<?php namespace tcCore;

use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class RankingQuestionAnswer extends BaseModel {

    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'ranking_question_answers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['answer'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function rankingQuestionAnswerLinks() {
        return $this->hasMany('tcCore\RankingQuestionAnswerLink', 'ranking_question_answer_id');
    }

    public function questions() {
        return $this->belongsToMany('tcCore\Question',
            'ranking_question_answer_links',
            'ranking_question_answer_id',
            'ranking_question_id'
        )->withPivot(
            [
                $this->getCreatedAtColumn(),
                $this->getUpdatedAtColumn(),
                // $this->getDeletedAtColumn()
            ]
        )->wherePivot('ranking_question_answer_links.deleted_at', null);
        // )->wherePivot($this->getDeletedAtColumn(), null);
    }

    public function rankingQuestions() {
        return $this->belongsToMany('tcCore\RankingQuestion',
            'ranking_question_answer_links',
            'ranking_question_answer_id',
            'ranking_question_id'
        )->withPivot(
            [
                $this->getCreatedAtColumn(),
                $this->getUpdatedAtColumn(),
                // $this->getDeletedAtColumn()
            ]
        )->wherePivot('ranking_question_answer_links.deleted_at', null);
        // )->wherePivot($this->getDeletedAtColumn(), null);
    }

    public function duplicate(RankingQuestion $rankingQuestionQuestion, array $attributes) {
        $rankingQuestionQuestionAnswer = $this->replicate();
        $rankingQuestionQuestionAnswer->fill($attributes);

        $rankingQuestionQuestionAnswer->save();

        return $rankingQuestionQuestionAnswer;
    }

    public function isUsed($ignoreRelationTo) {
        $uses = $this->rankingQuestionAnswerLinks()->withTrashed();
        if ($ignoreRelationTo instanceof RankingQuestion) {
            $ignoreRelationTo->where('ranking_question_id', '!=', $ignoreRelationTo->getKey());
        }

        if ($ignoreRelationTo instanceof RankingQuestionAnswerLink) {
            $ignoreRelationTo->where('ranking_question_id', '!=', $ignoreRelationTo->getAttribute('ranking_question_id'));
        }

        return $uses->count() > 0;
    }
}

<?php namespace tcCore;

use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class CompletionQuestionAnswer extends BaseModel {

    use SoftDeletes;


    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'completion_question_answers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['tag', 'answer', 'correct', 'order'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function completionQuestionAnswerLinks() {
        return $this->hasMany('tcCore\CompletionQuestionAnswerLink', 'completion_question_answer_id');
    }

    public function questions() {
        return $this->belongsToMany(
            'tcCore\Question',
            'completion_question_answer_links',
            'completion_question_answer_id',
            'completion_question_id'
        )->withPivot(
            [
                $this->getCreatedAtColumn(),
                $this->getUpdatedAtColumn(),
                // $this->getDeletedAtColumn()
            ]
        // )->wherePivot($this->getDeletedAtColumn(), null);
        )->wherePivot('completion_question_answer_links.deleted_at', null);
    }

    public function completionChoiceQuestions() {
        return $this->belongsToMany(
            'tcCore\CompletionQuestion',
            'completion_question_answer_links',
            'completion_question_answer_id',
            'completion_question_id'
        )->withPivot(
            [
                $this->getCreatedAtColumn(),
                $this->getUpdatedAtColumn(),
                // $this->getDeletedAtColumn()
            ]
        // )->wherePivot($this->getDeletedAtColumn(), null);
        )->wherePivot('completion_question_answer_links.deleted_at', null);
    }

    public function duplicate(CompletionQuestion $completionQuestion, array $attributes) {
        $completionQuestionAnswer = $this->replicate();
        $completionQuestionAnswer->fill($attributes);

        $completionQuestionAnswer->save();

        return $completionQuestionAnswer;
    }


    public function isUsed($ignoreRelationTo, $withTrashed = true) {
        if($withTrashed) {
            $uses = $this->completionQuestionAnswerLinks()->withTrashed();
        }
        else{
            $uses = $this->completionQuestionAnswerLinks();
        }

        if ($ignoreRelationTo instanceof CompletionQuestion) {
            $ignoreRelationTo->where('completion_question_id', '!=', $ignoreRelationTo->getKey());
        }

        if ($ignoreRelationTo instanceof CompletionQuestionAnswerLink) {
            $ignoreRelationTo->where('completion_question_id', '!=', $ignoreRelationTo->getAttribute('completion_question_id'));
        }

        return $uses->count() > 0;
    }
}

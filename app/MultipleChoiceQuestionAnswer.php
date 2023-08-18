<?php 

namespace tcCore;

use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class MultipleChoiceQuestionAnswer extends BaseModel {

    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'multiple_choice_question_answers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['answer', 'score'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $reorder = true;

    public function multipleChoiceQuestionAnswerLinks() {
        return $this->hasMany('tcCore\MultipleChoiceQuestionAnswerLink', 'multiple_choice_question_answer_id');
    }

    public function questions() {
        return $this->belongsToMany(
            'tcCore\Question',
            'multiple_choice_question_answer_links',
            'multiple_choice_question_answer_id',
            'multiple_choice_question_id'
        )->withPivot(
            [
                $this->getCreatedAtColumn(),
                $this->getUpdatedAtColumn()
            ]
        )->wherePivot(
            'multiple_choice_question_answer_links.deleted_at', null
        );
    }

    public function multipleChoiceQuestions() {
        return $this->belongsToMany(
            'tcCore\MultipleChoiceQuestion',
            'multiple_choice_question_answer_links',
            'multiple_choice_question_answer_id',
            'multiple_choice_question_id'
        )->withPivot(
            [
                $this->getCreatedAtColumn(),
                $this->getUpdatedAtColumn()
            ]
        )->wherePivot(
            'multiple_choice_question_answer_links.deleted_at', null
        );
    }

    public function duplicate(MultipleChoiceQuestion $multipleChoiceQuestion, array $attributes) {
        $multipleChoiceQuestionAnswer = $this->replicate();
        $multipleChoiceQuestionAnswer->fill($attributes);

        $multipleChoiceQuestionAnswer->save();

        return $multipleChoiceQuestionAnswer;
    }

    public function isUsed($ignoreRelationTo) {
        $uses = $this->multipleChoiceQuestionAnswerLinks()->withTrashed();

        if ($ignoreRelationTo instanceof MultipleChoiceQuestion) {
            $ignoreRelationTo->where('multiple_choice_question_id', '!=', $ignoreRelationTo->getKey());
        }

        if ($ignoreRelationTo instanceof MultipleChoiceQuestionAnswerLink) {
            $ignoreRelationTo->where('multiple_choice_question_id', '!=', $ignoreRelationTo->getAttribute('multiple_choice_question_id'));
        }

        return $uses->count() > 0;
    }
}

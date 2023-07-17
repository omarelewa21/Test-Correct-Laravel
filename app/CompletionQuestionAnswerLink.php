<?php namespace tcCore;

use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Lib\Models\CompositePrimaryKeyModel;
use tcCore\Lib\Models\CompositePrimaryKeyModelSoftDeletes;

class CompletionQuestionAnswerLink extends CompositePrimaryKeyModel
{

    use CompositePrimaryKeyModelSoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $casts = ['deleted_at' => 'datetime',];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'completion_question_answer_links';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['order'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = ['completion_question_id', 'completion_question_answer_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function completionQuestion() {
        return $this->belongsTo('tcCore\CompletionQuestion');
    }

    public function completionQuestionAnswer() {
        return $this->belongsTo('tcCore\CompletionQuestionAnswer');
    }

    public function duplicate($parent, $attributes, $ignore = null) {
        $completionQuestionAnswerLink = $this->replicate();
        $completionQuestionAnswerLink->fill($attributes);

        if($parent instanceof CompletionQuestion) {
            $completionQuestionAnswerLink->setAttribute('completion_question_answer_id', $this->getAttribute('completion_question_answer_id'));
            if ($parent->completionQuestionAnswerLinks()->save($completionQuestionAnswerLink) === false) {
                return false;
            }
        } elseif($parent instanceof CompletionQuestionAnswer) {
            $completionQuestionAnswerLink->setAttribute('completion_question_id', $this->getAttribute('completion_question_id'));
            if ($parent->completionQuestionAnswerLinks()->save($completionQuestionAnswerLink) === false) {
                return false;
            }
        } else {
            return false;
        }

        return $completionQuestionAnswerLink;
    }
}

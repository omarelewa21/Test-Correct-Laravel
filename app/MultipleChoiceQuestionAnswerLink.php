<?php 

namespace tcCore;

use Illuminate\Support\Facades\Log;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Lib\Models\CompositePrimaryKeyModel;
use tcCore\Lib\Models\CompositePrimaryKeyModelSoftDeletes;

class MultipleChoiceQuestionAnswerLink extends CompositePrimaryKeyModel {

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
    protected $table = 'multiple_choice_question_answer_links';

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
    protected $primaryKey = ['multiple_choice_question_id', 'multiple_choice_question_answer_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $reorder = true;

    public static function boot()
    {
        parent::boot();

        static::saved(function($multipleChoiceQuestionAnswerLink)
        {
            // If the order of an answer changes,
            if ($multipleChoiceQuestionAnswerLink->doReorder() && ($multipleChoiceQuestionAnswerLink->getOriginal('order') != $multipleChoiceQuestionAnswerLink->getAttribute('order')
                    || $multipleChoiceQuestionAnswerLink->getOriginal('multiple_choice_question_id') != $multipleChoiceQuestionAnswerLink->getAttribute('multiple_choice_question_id'))) {
                if ($multipleChoiceQuestionAnswerLink->multipleChoiceQuestion !== null) {
                    $multipleChoiceQuestionAnswerLink->multipleChoiceQuestion->reorder($multipleChoiceQuestionAnswerLink);
                }
            }
        });
    }

    public function setReorder($reorder) {
        $this->reorder = ($reorder === true);
    }

    public function doReorder() {
        return $this->reorder;
    }

    public function multipleChoiceQuestion() {
        return $this->belongsTo('tcCore\MultipleChoiceQuestion', 'multiple_choice_question_id');
    }

    public function multipleChoiceQuestionAnswer() {
        return $this->belongsTo('tcCore\MultipleChoiceQuestionAnswer', 'multiple_choice_question_answer_id');
    }

    public function duplicate($parent, $attributes, $ignore = null) {
        $multipleChoiceQuestionAnswerLink = $this->replicate();
        $multipleChoiceQuestionAnswerLink->fill($attributes);

        if($parent instanceof MultipleChoiceQuestion) {
            $multipleChoiceQuestionAnswerLink->setAttribute('multiple_choice_question_answer_id', $this->getAttribute('multiple_choice_question_answer_id'));
            if ($parent->multipleChoiceQuestionAnswerLinks()->save($multipleChoiceQuestionAnswerLink) === false) {
                return false;
            }
        } elseif($parent instanceof MultipleChoiceQuestionAnswer) {
            $multipleChoiceQuestionAnswerLink->setAttribute('multiple_choice_question_id', $this->getAttribute('multiple_choice_question_id'));
            if ($parent->multipleChoiceQuestionAnswerLinks()->save($multipleChoiceQuestionAnswerLink) === false) {
                return false;
            }
        } else {
            return false;
        }

        return $multipleChoiceQuestionAnswerLink;
    }
}

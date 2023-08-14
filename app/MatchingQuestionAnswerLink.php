<?php namespace tcCore;

use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Lib\Models\CompositePrimaryKeyModel;
use tcCore\Lib\Models\CompositePrimaryKeyModelSoftDeletes;

class MatchingQuestionAnswerLink extends CompositePrimaryKeyModel {

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
    protected $table = 'matching_question_answer_links';

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
    protected $primaryKey = ['matching_question_id', 'matching_question_answer_id'];

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

        static::saved(function($matchingQuestionAnswerLink)
        {
            // If the order of an answer changes,

            // 20190513 Not needed anymore as all the answers are saved at once AND we delete all answers first

//            if ($matchingQuestionAnswerLink->doReorder() && ($matchingQuestionAnswerLink->getOriginal('order') != $matchingQuestionAnswerLink->getAttribute('order')
//                    || $matchingQuestionAnswerLink->getOriginal('matching_question_id') != $matchingQuestionAnswerLink->getAttribute('matching_question_id'))) {
//                if ($matchingQuestionAnswerLink->matchingQuestion !== null) {
//                    $matchingQuestionAnswerLink->matchingQuestion->reorder($matchingQuestionAnswerLink);
//                }
//            }
        });
    }

    public function setReorder($reorder) {
        $this->reorder = ($reorder === true);
    }

    public function doReorder() {
        return $this->reorder;
    }

    public function matchingQuestion() {
        return $this->belongsTo('tcCore\MatchingQuestion');
    }

    public function matchingQuestionAnswer() {
        return $this->belongsTo('tcCore\MatchingQuestionAnswer');
    }

    public function duplicate($parent, $attributes, $ignore = null) {
        $matchingQuestionAnswerLink = $this->replicate();
        $matchingQuestionAnswerLink->fill($attributes);

        if($parent instanceof MatchingQuestion) {
            $matchingQuestionAnswerLink->setAttribute('matching_question_answer_id', $this->getAttribute('matching_question_answer_id'));
            if ($parent->matchingQuestionAnswerLinks()->save($matchingQuestionAnswerLink) === false) {
                return false;
            }
        } elseif($parent instanceof MatchingQuestionAnswer) {
            $matchingQuestionAnswerLink->setAttribute('matching_question_id', $this->getAttribute('matching_question_id'));
            if ($parent->matchingQuestionAnswerLinks()->save($matchingQuestionAnswerLink) === false) {
                return false;
            }
        } else {
            return false;
        }

        return $matchingQuestionAnswerLink;
    }
}

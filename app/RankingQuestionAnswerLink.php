<?php namespace tcCore;

use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Lib\Models\CompositePrimaryKeyModel;
use tcCore\Lib\Models\CompositePrimaryKeyModelSoftDeletes;

class RankingQuestionAnswerLink extends CompositePrimaryKeyModel {

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
    protected $table = 'ranking_question_answer_links';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['order', 'correct_order'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = ['ranking_question_id', 'ranking_question_answer_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $reorder = true;

    protected $preventReorder = false;

    public static function boot()
    {
        parent::boot();

        static::saved(function($rankingQuestionAnswerLink)
        {

            if(!$rankingQuestionAnswerLink->doPreventReorder()) {
                // If the order of an answer changes,
                if ($rankingQuestionAnswerLink->doReorder() && ($rankingQuestionAnswerLink->getOriginal('order') != $rankingQuestionAnswerLink->getAttribute('order')
                        || $rankingQuestionAnswerLink->getOriginal('ranking_question_id') != $rankingQuestionAnswerLink->getAttribute('ranking_question_id'))) {
                    if ($rankingQuestionAnswerLink->rankingQuestion !== null) {
                        $rankingQuestionAnswerLink->rankingQuestion->reorder($rankingQuestionAnswerLink, 'order');
                    }
                }
                if ($rankingQuestionAnswerLink->doReorder() && ($rankingQuestionAnswerLink->getOriginal('correct_order') != $rankingQuestionAnswerLink->getAttribute('correct_order')
                        || $rankingQuestionAnswerLink->getOriginal('ranking_question_id') != $rankingQuestionAnswerLink->getAttribute('ranking_question_id'))) {
                    if ($rankingQuestionAnswerLink->rankingQuestion !== null) {
                        $rankingQuestionAnswerLink->rankingQuestion->reorder($rankingQuestionAnswerLink, 'correct_order');
                    }
                }
            }
        });
    }

    public function setPreventReorder($prevent){
        $this->preventReorder = ($prevent === true);
    }

    protected function doPreventReorder(){
        return (bool) $this->preventReorder;
    }

    public function setReorder($reorder) {
        $this->reorder = ($reorder === true);
    }

    public function doReorder() {
        return $this->reorder;
    }

    public function rankingQuestion() {
        return $this->belongsTo('tcCore\RankingQuestion');
    }

    public function rankingQuestionAnswer() {
        return $this->belongsTo('tcCore\RankingQuestionAnswer');
    }

    public function duplicate($parent, $attributes, $ignore = null) {
        $rankingQuestionAnswerLink = $this->replicate();
        $rankingQuestionAnswerLink->fill($attributes);

        if($parent instanceof RankingQuestion) {
            $rankingQuestionAnswerLink->setAttribute('ranking_question_answer_id', $this->getAttribute('ranking_question_answer_id'));
            if ($parent->rankingQuestionAnswerLinks()->save($rankingQuestionAnswerLink) === false) {
                return false;
            }
        } elseif($parent instanceof RankingQuestionAnswer) {
            $rankingQuestionAnswerLink->setAttribute('ranking_question_id', $this->getAttribute('ranking_question_id'));
            if ($parent->rankingQuestionAnswerLinks()->save($rankingQuestionAnswerLink) === false) {
                return false;
            }
        } else {
            return false;
        }

        return $rankingQuestionAnswerLink;
    }
}

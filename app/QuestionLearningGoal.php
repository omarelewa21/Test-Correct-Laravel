<?php namespace tcCore;

use tcCore\Lib\Models\CompositePrimaryKeyModel;
use tcCore\Lib\Models\CompositePrimaryKeyModelSoftDeletes;

class QuestionLearningGoal extends CompositePrimaryKeyModel {

    use CompositePrimaryKeyModelSoftDeletes;

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
    protected $table = 'question_attainments';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['attainment_id', 'question_id'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = ['attainment_id', 'question_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function learningGoal() {
        return $this->belongsTo('tcCore\LearningGoal');
    }

    public function question() {
        return $this->belongsTo('tcCore\Question');
    }

    public function duplicate($parent, $attributes, $ignore = null) {
        $questionLearningGoal = $this->replicate();
        $questionLearningGoal->fill($attributes);

        if($parent instanceof Question) {
            $questionLearningGoal->setAttribute('attainment_id', $this->getAttribute('attainment_id'));
            if ($parent->questionLearningGoals()->save($questionLearningGoal) === false) {
                return false;
            }
        } elseif($parent instanceof Attainment) {
            $questionLearningGoal->setAttribute('question_id', $this->getAttribute('question_id'));
            if ($parent->questionLearningGoals()->save($questionLearningGoal) === false) {
                return false;
            }
        } else {
            return false;
        }

        return $questionLearningGoal;
    }

    public function scopeStrict($query)
    {
        $query->select('question_attainments.attainment_id as attainment_id','question_attainments.question_id','question_attainments.deleted_at')->join('attainments','question_attainments.attainment_id','attainments.id')->where('is_learning_goal', '=',true);
    }
}

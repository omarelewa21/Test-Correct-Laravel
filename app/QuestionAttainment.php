<?php namespace tcCore;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use tcCore\Lib\Models\CompositePrimaryKeyModel;
use tcCore\Lib\Models\CompositePrimaryKeyModelSoftDeletes;
use tcCore\Scopes\QuestionAttainmentScope;

class QuestionAttainment extends CompositePrimaryKeyModel {

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

    public function attainment() {
        return $this->belongsTo('tcCore\Attainment');
    }

    public function question() {
        return $this->belongsTo('tcCore\Question');
    }

    public function duplicate($parent, $attributes, $ignore = null) {
        $questionAttainment = $this->replicate();
        $questionAttainment->fill($attributes);

        if($parent instanceof Question) {
            $questionAttainment->setAttribute('attainment_id', $this->getAttribute('attainment_id'));
            if ($parent->questionAttainments()->save($questionAttainment) === false) {
                return false;
            }
        } elseif($parent instanceof Attainment) {
            $questionAttainment->setAttribute('question_id', $this->getAttribute('question_id'));
            if ($parent->questionAttainments()->save($questionAttainment) === false) {
                return false;
            }
        } else {
            return false;
        }

        return $questionAttainment;
    }

    public function scopeStrict($query)
    {
        $query->select('question_attainments.attainment_id as attainment_id','question_attainments.question_id','question_attainments.deleted_at')->join('attainments','question_attainments.attainment_id','attainments.id')->where('is_learning_goal', '=',false);
    }
}

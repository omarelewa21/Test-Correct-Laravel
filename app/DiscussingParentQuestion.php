<?php namespace tcCore;

use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class DiscussingParentQuestion extends BaseModel {

    use SoftDeletes;

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
    protected $table = 'discussing_parent_questions';

    protected $appends= ['group_question_uuid'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['test_take_id', 'group_question_id', 'level'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function answer() {
        return $this->belongsTo('tcCore\TestTake');
    }

    public function groupQuestion() {
        return $this->belongsTo('tcCore\GroupQuestion');
    }

    public function getGroupQuestionUuidAttribute() {
        return $this->groupQuestion->uuid;
    }
}

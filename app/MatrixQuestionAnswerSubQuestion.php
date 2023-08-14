<?php namespace tcCore;

use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Lib\Models\CompositePrimaryKeyModel;
use tcCore\Lib\Models\CompositePrimaryKeyModelSoftDeletes;

class MatrixQuestionAnswerSubQuestion extends CompositePrimaryKeyModel
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
    protected $table = 'matrix_question_answer_sub_questions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = ['matrix_question_answer_id', 'matrix_question_sub_question_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function matrixQuestionSubQuestion() {
        return $this->belongsTo(MatrixQuestionSubQuestion::class);
    }

    public function matrixQuestionAnswer() {
        return $this->belongsTo(MatrixQuestionAnswer::class);
    }

}

<?php namespace tcCore;

use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class MatrixQuestionAnswer extends BaseModel {

    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'matrix_question_answers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['answer', 'order'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function matrixQuestion()
    {
        return $this->belongsTo(MatrixQuestion::class);
    }

    public function duplicate(MatrixQuestion $matrixQuestion, array $attributes) {
        $matrixQuestionAnswer = $this->replicate();
        $matrixQuestionAnswer->fill($attributes);
        $matrixQuestionAnswer->matrix_question_id = $matrixQuestion->getKey();

        $matrixQuestionAnswer->save();

        return $matrixQuestionAnswer;
    }


    public function isUsed($ignoreRelationTo, $withTrashed = true) {
        if($withTrashed) {
            $uses = $this->matrixQuestion()->withTrashed();
        }
        else{
            $uses = $this->matrixQuestion();
        }

        if ($ignoreRelationTo instanceof MatrixQuestion) {
            $ignoreRelationTo->where('matrix_question_id', '!=', $ignoreRelationTo->getKey());
        }

        return $uses->count() > 0;
    }
}

<?php namespace tcCore;

use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Lib\Models\CompositePrimaryKeyModel;
use tcCore\Lib\Models\CompositePrimaryKeyModelSoftDeletes;

class MatrixQuestionSubQuestion extends BaseModel
{

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
    protected $table = 'matrix_question_sub_questions';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['sub_question','order','score'];

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

    public function matrixQuestionAnswers() {
        return $this->belongsToMany(
            MatrixQuestionAnswer::class,
            'matrix_question_answer_sub_questions',
            'matrix_question_sub_question_id',
            'matrix_question_answer_id'
        )->withPivot(
            [
                $this->getCreatedAtColumn(),
                $this->getUpdatedAtColumn(),
                $this->getDeletedAtColumn()
            ]
        )->wherePivot($this->getDeletedAtColumn(), null);
    }

    public function matrixQuestionAnswerSubQuestions()
    {
        return $this->hasMany(MatrixQuestionAnswerSubQuestion::class);
    }

    public function duplicate($parent, $attributes, $answerReferences = null) {
        $matrixQuestionSubQuestion = $this->replicate();
        $matrixQuestionSubQuestion->fill($attributes);

        if($parent instanceof MatrixQuestion) {
            if ($parent->matrixQuestionSubQuestions()->save($matrixQuestionSubQuestion) === false) {
                return false;
            }
        } else {
            return false;
        }

        if(null !== $answerReferences && count($answerReferences) > 0){
            foreach($this->matrixQuestionAnswers as $matrixQuestionAnswer) {
                $matrixQuestionAnswerSubQuestion = new MatrixQuestionAnswerSubQuestion();
                $matrixQuestionAnswerSubQuestion->matrix_question_sub_question_id = $matrixQuestionSubQuestion->getKey();
                $matrixQuestionAnswerSubQuestion->matrixQuestionAnsers()->save($answerReferences[$matrixQuestionAnswer->getKey()]);
            }
        }

        return $matrixQuestionSubQuestion;
    }
}

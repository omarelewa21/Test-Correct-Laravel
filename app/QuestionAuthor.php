<?php namespace tcCore;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use tcCore\Jobs\CountTeacherQuestions;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Lib\Models\CompositePrimaryKeyModel;
use tcCore\Lib\Models\CompositePrimaryKeyModelSoftDeletes;

class QuestionAuthor extends CompositePrimaryKeyModel {

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
    protected $table = 'question_authors';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['question_id', 'user_id'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = ['question_id', 'user_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public static function boot()
    {
        parent::boot();

        static::saving(function(QuestionAuthor $questionAuthor)
        {
            Queue::push(new CountTeacherQuestions($questionAuthor->user));
        });

        static::deleted(function(QuestionAuthor $questionAuthor)
        {
            Queue::push(new CountTeacherQuestions($questionAuthor->user));
        });
    }

    public function question() {
        return $this->belongsTo('tcCore\Question');
    }

    public function user() {
        return $this->belongsTo('tcCore\User');
    }

    public static function addAuthorToQuestion($question) {
        $question = $question->getQuestionInstance();

        $questionAuthor = static::withTrashed()->where('user_id', Auth::id())->where('question_id', $question->getKey())->first();

        if ($questionAuthor === null) {
            $questionAuthor = new QuestionAuthor(['user_id' => Auth::id(), 'question_id' => $question->getKey()]);
            if (!$questionAuthor->save()) {
                return false;
            }
        } else {
            if(!$questionAuthor->restore()) {
                return false;
            }
        }

        return true;
    }
}

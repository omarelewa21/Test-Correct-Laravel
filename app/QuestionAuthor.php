<?php namespace tcCore;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Queue;
use tcCore\Http\Controllers\AuthorsController;
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
    protected $casts = ['deleted_at' => 'datetime',];

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
            if(is_null($questionAuthor->user)){
                return;
            }
            Queue::push(new CountTeacherQuestions($questionAuthor->user));
        });

        static::deleted(function(QuestionAuthor $questionAuthor)
        {
            if(is_null($questionAuthor->user)){
                return;
            }
            Queue::push(new CountTeacherQuestions($questionAuthor->user));
        });
    }

    public function question() {
        return $this->belongsTo('tcCore\Question');
    }

    public function user() {
        return $this->belongsTo('tcCore\User');
    }

    public static function addAuthorToQuestion($question,$authorId = false) {
        $question = $question->getQuestionInstance();
        $userId = $authorId?:Auth::id();
        if(Auth::user()->isInExamSchool()&&!$authorId){
            $user = AuthorsController::getCentraalExamenAuthor();
            $userId = $user?$user->getKey():Auth::id();
        }

        $questionAuthor = static::withTrashed()->where('user_id', $userId)->where('question_id', $question->getKey())->first();

        if ($questionAuthor === null) {
            $questionAuthor = new QuestionAuthor(['user_id' => $userId, 'question_id' => $question->getKey()]);
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

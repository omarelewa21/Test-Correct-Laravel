<?php namespace tcCore;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use tcCore\Jobs\PValues\CalculatePValueForAnswer;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Scopes\ArchivedScope;

class AnswerRating extends BaseModel
{

    use SoftDeletes;

    const TYPE_STUDENT = 'STUDENT';
    const TYPE_TEACHER = 'TEACHER';
    const TYPE_SYSTEM = 'SYSTEM';

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
    protected $table = 'answer_ratings';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['answer_id', 'user_id', 'test_take_id', 'type', 'rating', 'json'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $casts = [
        'json' => 'array'
    ];

    public static function boot()
    {
        parent::boot();

        // Progress additional answers
        static::saved(function (AnswerRating $answerRating) {
            $answer = $answerRating->answer;
            $answer->setAttribute('final_rating', null);
            $answer->save();
            Queue::push(new CalculatePValueForAnswer($answer));
        });
    }

    public function user()
    {
        return $this->belongsTo('tcCore\User');
    }

    public function answer()
    {
        return $this->belongsTo('tcCore\Answer');
    }

    public function testTake()
    {
        return $this->belongsTo('tcCore\TestTake');
    }

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        $roles = $this->getUserRoles();

        if (in_array('Teacher', $roles)) {

            $query->where(function ($query) {
                $query->whereIn('test_take_id', function ($query) {
                    $query->select('id')
                        ->from(with(new TestTake())->getTable())
                        ->where('user_id', Auth::id())
                        ->where('deleted_at', null);
                })
                    ->orWhere('user_id', Auth::id());
            });
        } elseif (in_array('Student', $roles)) {
            $query->where('user_id', Auth::id());
        }

        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'answer_id':
                    $value = Answer::whereUuid($value)->first()->getKey();
                    if (is_array($value)) {
                        $query->whereIn('answer_id', $value);
                    } else {
                        $query->where('answer_id', '=', $value);
                    }
                    break;
                case 'user_id':
                    $value = User::whereUuid($value)->first()->getKey();
                    if (is_array($value)) {
                        $query->whereIn('user_id', $value);
                    } else {
                        $query->where('user_id', '=', $value);
                    }
                    break;
                case 'test_take_id':
                    if (is_array($value)) {
                        $query->whereIn('test_take_id', $value);
                    } else {
                        $query->where('test_take_id', '=', $value);
                    }
                    break;
                case 'discussing_at_test_take_id':
                    $value = TestTake::whereUuid($value)->first()->getKey();

                    $query->where('test_take_id', '=', $value);

                    $parentRows = DiscussingParentQuestion::where('test_take_id', $value)->orderBy('level')->get();
                    $parents = null;
                    foreach ($parentRows as $answerParentQuestion) {
                        if ($parents !== null) {
                            $parents .= '.';
                        }
                        $parents .= $answerParentQuestion->getAttribute('group_question_id');
                    }

                    $questionId = TestTake::where('id', $value)->value('discussing_question_id');
                    $answers = Answer::whereIn('test_participant_id', function ($query) use ($value) {
                        $testParticipant = new TestParticipant();
                        $query->select($testParticipant->getKeyName())->from($testParticipant->getTable())->where('test_take_id', $value);
                    })->where('question_id', $questionId)->with('answerParentQuestions')->get();

                    $answerIds = array();
                    foreach ($answers as $answer) {
                        // Decide if this is question that is currently being discussed
                        $answerParents = null;
                        foreach ($answer->answerParentQuestions as $answerParentQuestion) {
                            if ($answerParents !== null) {
                                $answerParents .= '.';
                            }
                            $answerParents .= $answerParentQuestion->getAttribute('group_question_id');
                        }

                        if ($parents == $answerParents) {
                            $answerIds[] = $answer->getKey();
                        }
                    }

                    $query->whereIn('answer_id', $answerIds);
                    break;
                case 'type':
                    if (is_array($value)) {
                        $query->whereIn('type', $value);
                    } else {
                        $query->where('type', '=', $value);
                    }
                    break;
                case 'rated':
                    if ($value) {
                        $query->whereNotNull('rating');
                    } else {
                        $query->where('rating', null);
                    }
                    break;
                case 'current_answer_rating':
                    if ($value && array_key_exists('discussing_at_test_take_id', $filters) && array_key_exists('user_id', $filters)) {
                        $query->whereIn('id',
                            TestParticipant::select('discussing_answer_rating_id')
                                ->whereIn('user_id',
                                    User::whereUuid($filters['user_id'])->select('id')
                                )->whereIn('test_take_id',
                                    TestTake::whereUuid($filters['discussing_at_test_take_id'])->select('id')
                                        ->withoutGlobalScope(ArchivedScope::class)
                                )
                        );
                    }
                    break;
            }
        }

        foreach ($sorting as $key => $value) {
            switch (strtolower($value)) {
                case 'id':
                case 'answer_id':
                case 'user_id':
                case 'test_take_id':
                case 'type':
                case 'rating':
                    $key = $value;
                    $value = 'asc';
                    break;
                case 'asc':
                case 'desc':
                    break;
                default:
                    $value = 'asc';
            }

            switch (strtolower($key)) {
                case 'id':
                case 'answer_id':
                case 'user_id':
                case 'test_take_id':
                case 'type':
                case 'rating':
                    $query->orderBy($key, $value);
                    break;
            }

        }

        return $query;
    }
}

<?php namespace tcCore;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Auth;
use tcCore\Lib\Models\BaseModel;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;
use tcCore\Traits\UuidTrait;

class Answer extends BaseModel
{

    use SoftDeletes;
    use UuidTrait;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

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
    protected $table = 'answers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['test_participant_id', 'question_id', 'json', 'time', 'note', 'closed', 'closed_group'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $parentGroupQuestions;

    public static function boot()
    {
        parent::boot();

        static::created(function (Answer $answer) {
            if ($answer->parentGroupQuestions) {
                $level = 1;

                foreach ($answer->parentGroupQuestions as $parent) {
                    AnswerParentQuestion::create([
                        'answer_id' => $answer->getKey(), 'group_question_id' => $parent, 'level' => $level
                    ]);
                    $level++;
                }
            }
        });

        // Progress additional answers
        static::updating(function (Answer $answer) {
            if (($testParticipant = $answer->testParticipant) !== null && ($user = $testParticipant->user) !== null && $user->getAttribute('id') == Auth::id() && $answer->getAttribute('json') !== null) {
                $answer->setAttribute('done', true);
            }
        });
    }

    public function setParentGroupQuestions($parentGroupQuestions)
    {
        $this->parentGroupQuestions = $parentGroupQuestions;
    }

    public function testParticipant()
    {
        return $this->belongsTo('tcCore\TestParticipant');
    }

    public function answerParentQuestions()
    {
        return $this->hasMany('tcCore\AnswerParentQuestion', 'answer_id');
    }

    public function question()
    {
        return $this->belongsTo('tcCore\Question');
    }

    public function answerRatings()
    {
        return $this->hasMany('tcCore\AnswerRating');
    }

    public function pValue()
    {
        return $this->hasMany('tcCore\PValue');
    }

    public function calculateFinalRating()
    {
        $scores = [];
        foreach ($this->answerRatings as $answerRating) {
            if ($answerRating->getAttribute('rating') === null) {
                continue;
            }

            if ($answerRating->getAttribute('type') == 'STUDENT' && $answerRating->getAttribute('rating') !== null) {
                $scores[$answerRating->getAttribute('type')][] = $answerRating->getAttribute('rating');
            } elseif ($answerRating->getAttribute('type') != 'STUDENT') {
                $scores[$answerRating->getAttribute('type')] = $answerRating->getAttribute('rating');
            }
        }

        if (array_key_exists('TEACHER', $scores)) {
            return $scores['TEACHER'];
        } elseif (array_key_exists('SYSTEM', $scores)) {
            return $scores['SYSTEM'];
        } elseif (array_key_exists('STUDENT', $scores) && count($scores['STUDENT']) > 1) {
            $scores = array_unique($scores['STUDENT']);
            if (count($scores) === 1) {
                return $scores['0'];
            } else {
                return null;
            }
        } else {
            return null;
        }
    }

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'id':
                    if (is_array($value)) {
                        $query->whereIn('id', $value);
                    } else {
                        $query->where('id', '=', $value);
                    }
                    break;
                case 'question_id':
                    if (UUid::isValid($value)) {
                        $value = Question::findByUuid($value)->getKey();
                    }
                    if (is_array($value)) {
                        $query->whereIn('question_id', $value);
                    } else {
                        $query->where('question_id', '=', $value);
                    }
                    break;
                case 'test_participant_id':
                    if (Uuid::isValid($value)) {
                        $value = TestParticipant::whereUuid($value)->first()->getKey();
                    }
                    if (is_array($value)) {
                        $query->whereIn('test_participant_id', $value);
                    } else {
                        $query->where('test_participant_id', '=', $value);
                    }
                    break;
            }
        }

        foreach ($sorting as $key => $value) {
            switch (strtolower($value)) {
                case 'id':
                case 'order':
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
                case 'order':
                    $query->orderBy($key, $value);
                    break;
            }
        }
    }

    public function fill(array $attributes)
    {
        if (array_key_exists('add_time', $attributes)) {
            if (array_key_exists('time', $attributes)) {
                $attributes['time'] += $attributes['add_time'];
            } else {
                if (($time = $this->getAttribute('time')) !== null) {
                    $attributes['time'] = $time + $attributes['add_time'];
                } else {
                    $attributes['time'] = $attributes['add_time'];
                }
            }

            unset($attributes['add_time']);
        }

        parent::fill($attributes);
    }

//    public function getCloseableGroupAttribute()
//    {
//        return !!(optional(
//            optional(
//                optional(
//                    $this->parentGroupQuestions
//                )->first()
//            )->groupQuestion
//        )->closeable == 1);
//    }


}

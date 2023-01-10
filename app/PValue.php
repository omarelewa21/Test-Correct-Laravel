<?php namespace tcCore;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use tcCore\Jobs\SendExceptionMail;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Mail;
use tcCore\Traits\TimeSerieTrait;

class PValue extends BaseModel
{

    use TimeSerieTrait;

    use SoftDeletes;

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
    protected $table = 'p_values';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'answer_id', 'question_id', 'test_participant_id', 'period_id', 'subject_id', 'school_class_id',
        'education_level_id', 'score', 'max_score', 'education_level_year'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * UserIds of users that should be attached to this p-value
     *
     * @var array
     */
    protected $users;

    /**
     * AttainmentIds of attainments that should be attached to this p-value
     *
     * @var array
     */
    protected $attainments;

    public static function boot()
    {
        parent::boot();

        // Progress additional answers
        static::saved(function (PValue $pValue) {
            if ($pValue->users !== null) {
                $pValue->savePValueUsers();
            }

            if ($pValue->attainments !== null) {
                $pValue->savePValueAttainments();
            }
        });
    }

    public function answer()
    {
        return $this->belongsTo('tcCore\Answer');
    }

    public function testParticipant()
    {
        return $this->belongsTo('tcCore\TestParticipant');
    }

    public function question()
    {
        return $this->belongsTo('tcCore\Question');
    }

    public function period()
    {
        return $this->belongsTo('tcCore\Period');
    }

    public function schoolClass()
    {
        return $this->belongsTo('tcCore\SchoolClass')->withTrashed();
    }

    public function educationLevel()
    {
        return $this->belongsTo('tcCore\EducationLevel');
    }

    public function subject()
    {
        return $this->belongsTo('tcCore\Subject');
    }

    public function users()
    {
        return $this->hasMany('tcCore\PValueUser')->withTrashed();
    }

    public function savePValueUsers()
    {
        $users = $this->users()->withTrashed()->get();
        $this->syncTcRelation($users, $this->users, 'user_id', function ($pValue, $user) {
            $line = __LINE__;
            //TCP-335
            try {
                PValueUser::create(['user_id' => $user, 'p_value_id' => $pValue->getKey()]);
            } catch (\Throwable $th) {

                $existingPValueUser = PValueUser::where(['user_id' => $user, 'p_value_id' => $pValue->getKey()]);
                $error = null;
                if (is_null($existingPValueUser)) {
                    // strange error as we should have been able to create the pValueUser as it wasn't there yet, but still we got an error
                    $error = sprintf('Error while trying to create a PValueUser, error %s (user_id: %s, p_value_id: %s)',
                        $th->getMessage(),
                        $user,
                        $pValue->getKey()
                    );
                }
                if (null !== $error) {
                    Bugsnag::notifyException(new \LogicException($error));

                    dispatch_now(new SendExceptionMail($error, __FILE__, $line, [], 'PValueUser error'));
                }
            }
        });

        $this->users = null;
    }

    public function attainments()
    {
        return $this->hasMany('tcCore\PValueAttainment');
    }

    public function savePValueAttainments()
    {
        $attainments = $this->attainments()->withTrashed()->get();
        $this->syncTcRelation($attainments, $this->attainments, 'attainment_id', function ($pValue, $attainment) {
            //TCP-335
            try {
                PValueAttainment::create(['attainment_id' => $attainment, 'p_value_id' => $pValue->getKey()]);
            } catch (\Throwable $th) {
                $existingPValueAttainment = PValueAttainment::where([
                    'attainment_id' => $attainment, 'p_value_id' => $pValue->getKey()
                ]);
                if (is_null($existingPValueAttainment)) {
                    $body = 'Error in PValue.php: The PValueUser could not be created but the PValueUser with attainment_id "' . $attainment . '" and p_value_id "' . $pValue->getKey() . '" could not be created!';

                    Bugsnag::notifyException(new \LogicException($body));

                    Mail::raw($body, function ($message) {
                        $message->to(env("MAIL_DEV_ADDRESS"), 'Auto Error Mailer');
                        $message->subject('PValueUser error');
                    });
                }
            }
        });

        $this->attainments = null;
    }

    public function fill(array $attributes)
    {
        parent::fill($attributes);

        if (array_key_exists('users', $attributes)) {
            $this->users = $attributes['users'];
        } elseif (array_key_exists('add_user', $attributes) || array_key_exists('delete_user', $attributes)) {
            $this->users = $this->users()->pluck('user_id')->all();
            if (array_key_exists('add_user', $attributes)) {
                array_push($this->users, $attributes['add_user']);
            }

            if (array_key_exists('delete_user', $attributes)) {
                if (($key = array_search($attributes['delete_user'], $this->users)) !== false) {
                    unset($this->users[$key]);
                }
            }
        }

        if (array_key_exists('attainments', $attributes)) {
            $this->attainments = $attributes['attainments'];
        } elseif (array_key_exists('add_attainment', $attributes) || array_key_exists('delete_attainment',
                $attributes)) {
            $this->attainments = $this->attainments()->pluck('attainment_id')->all();
            if (array_key_exists('add_attainment', $attributes)) {
                array_push($this->attainments, $attributes['add_attainment']);
            }

            if (array_key_exists('delete_attainment', $attributes)) {
                if (($key = array_search($attributes['delete_attainment'], $this->attainments)) !== false) {
                    unset($this->attainments[$key]);
                }
            }
        }
    }

    public function scopeFilter($query, $user, $periods, $educationLevelYears, $teachers, $isLearningGoal = null)
    {
        if ($periods->isEmpty() && $educationLevelYears->isEmpty() && $teachers->isEmpty()) {
            $levelAndYears = Educationlevel::getlatesteducationlevelandeducationlevelyearforstudent($user);

            $query
                ->educationLevelYearFilter($levelAndYears['education_level_years'])
                ->where('p_values.education_level_id', $levelAndYears ['education_level_id']);
        } else {
            $query
                ->periodFilter($periods)
                ->educationLevelYearFilter($educationLevelYears)
                ->teacherFilter($teachers);
        }
        $query->learningGoalOrAttainmentFilter($isLearningGoal);
    }


    public function scopePeriodFilter($query, $periods)
    {
        $query->when(
            $periods->isNotEmpty(),
            fn($q) => $q->whereIn('p_values.period_id', $periods->pluck('id'))
        );
    }

    public function scopeLearningGoalOrAttainmentFilter($query, $isLearningGoal = null)
    {
        if (!is_null($isLearningGoal)) {
            $query->where('attainments.is_learning_goal', $isLearningGoal);
        }
    }

    public function scopeEducationLevelYearFilter($query, $educationLevelYears)
    {
        $query->when(
            $educationLevelYears->isNotEmpty(),
            fn($q) => $q->whereIn('p_values.education_level_year', $educationLevelYears)
        );
    }

    public function scopeTeacherFilter($query, $teachers)
    {
        $query->when($teachers->isNotEmpty(), function ($q) use ($teachers) {
            $q->join('p_value_users', 'p_value_users.p_value_id', '=', 'p_values.id')
                ->whereIn('p_value_users.user_id', $teachers->pluck('id'));
        });
    }
}

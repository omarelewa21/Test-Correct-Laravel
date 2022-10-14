<?php namespace tcCore;

use Doctrine\DBAL\Query\QueryBuilder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use tcCore\Jobs\PValues\UpdatePValueUsers;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\Traits\UuidTrait;


class Teacher extends BaseModel
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
    protected $table = 'teachers';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'class_id', 'subject_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public static function boot()
    {
        parent::boot();


        static::created(function (Teacher $teacher) {
            Queue::push(new UpdatePValueUsers($teacher->getAttribute('class_id'), $teacher->getAttribute('subject_id'), $teacher->getAttribute('user_id'), null, null, null));
        });

        static::updated(function (Teacher $teacher) {
            if ($teacher->getAttribute('class_id') != $teacher->getOriginal('class_id') || $teacher->getAttribute('subject_id') != $teacher->getOriginal('subject_id') || $teacher->getAttribute('user_id') != $teacher->getOriginal('user_id')) {
                Queue::push(new UpdatePValueUsers($teacher->getAttribute('class_id'), $teacher->getAttribute('subject_id'), $teacher->getAttribute('user_id'), $teacher->getOriginal('class_id'), $teacher->getOriginal('subject_id'), $teacher->getOriginal('user_id')));
            }
        });

        static::deleted(function (Teacher $teacher) {
            Queue::push(new UpdatePValueUsers(null, null, null, $teacher->getOriginal('class_id'), $teacher->getOriginal('subject_id'), $teacher->getOriginal('user_id')));
        });
    }

    public function user()
    {
        return $this->belongsTo('tcCore\User');
    }

    public function schoolClass()
    {
        return $this->belongsTo('tcCore\SchoolClass', 'class_id');
    }

    public function schoolClassWithoutVisibleOnlyScope()
    {
        return $this->belongsTo('tcCore\SchoolClass', 'class_id')->withoutGlobalScope("visibleOnly");
    }

    public function subject()
    {
        return $this->belongsTo('tcCore\Subject');
    }

    public function tests()
    {
        return $this->hasMany(Test::class, 'author_id', 'user_id');
    }

    public function scopeCurrentSchoolLocation($query)
    {
        $schoolClasses = SchoolClass::where('school_location_id', Auth::user()->school_location_id)->get()->pluck('id');
        return $query->whereIn('class_id', $schoolClasses);
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
                case 'user_id':
                    if (is_array($value)) {
                        $query->whereIn('user_id', $value);
                    } else {
                        $query->where('user_id', '=', $value);
                    }
                    break;
                case 'class_id':
                    if (is_array($value)) {
                        $query->whereIn('class_id', $value);
                    } else {
                        $query->where('class_id', '=', $value);
                    }
                    break;
                case 'subject_id':
                    if (is_array($value)) {
                        $query->whereIn('subject_id', $value);
                    } else {
                        $query->where('subject_id', '=', $value);
                    }
                    break;
            }
        }

        foreach ($sorting as $key => $value) {
            switch (strtolower($value)) {
                case 'id':
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
                    $query->orderBy($key, $value);
                    break;
            }
        }
    }

    public function importLog()
    {
        return $this->hasOne(TeacherImportLog::class, 'teacher_id', 'id');
    }

    public function getUserObjectsForDistinctTeachers()
    {
        $distinctTeachers = Teacher::select('user_id')->distinct();
        return User::joinSub($distinctTeachers, 't1', function ($join) {
            $join->on('users.id', '=', 't1.user_id');
        })
            ->with('schoolLocation:id,uuid')
            ->whereNotNull('t1.user_id')
            ->whereNotNull('users.school_location_id')
            ->orderBy('users.name_first', 'asc')
            ->get()
            ->filter(function ($user) {
                return $user->schoolLocation;
            })
            ->map(function ($user) {
                return (object)[
                    'id'                 => $user->id,
                    'uuid'               => $user->uuid,
                    'name'               => preg_replace('!\\r?\\n?\\t!', "", str_replace('  ', ' ', trim(sprintf('%s %s %s (%s)', $user->name_first, $user->name_suffix, $user->name, $user->abbreviation)))),
                    'school_location_id' => $user->schoolLocation->uuid,
                    'subject_ids'        => $user->subjects(Subject::select('uuid'))->get()->map(function ($s) {
                        return $s->uuid;
                    })->toArray(),
                ];
            });

    }

    public static function getTeacherUsersForSchoolLocationInCurrentYear(SchoolLocation $schoolLocation)
    {
        return self::getTeacherUsersForSchoolLocation($schoolLocation)
            ->where(function ($query){
                $query->whereIn(
                    'teachers.class_id',
                    SchoolClass::select('id')->whereSchoolYearId(SchoolYearRepository::getCurrentSchoolYear()->getKey())->whereDemo(0)
                );
            });

    }

    public static function getTeacherUsersForSchoolLocationBySubjectInCurrentYear(SchoolLocation $schoolLocation, $subjectId)
    {
        return self::getTeacherUsersForSchoolLocation($schoolLocation)
            ->where(function ($query) use ($subjectId) {
                $query->where(
                    'teachers.subject_id',
                    $subjectId
                )->whereIn(
                    'teachers.class_id',
                    SchoolClass::select('id')->whereSchoolYearId(SchoolYearRepository::getCurrentSchoolYear()->getKey())->whereDemo(0)
                );
            });

    }

    public static function getTeacherUsersForSchoolLocationByBaseSubjectInCurrentYear(SchoolLocation $schoolLocation, $baseSubjectId)
    {
        return self::getTeacherUsersForSchoolLocation($schoolLocation)->where(function ($query) use ($baseSubjectId) {
            $query->whereIn(
                'teachers.subject_id',
                Subject::select('id')->whereBaseSubjectId($baseSubjectId)
            )->whereIn(
                'teachers.class_id',
                SchoolClass::select('id')->whereSchoolYearId(SchoolYearRepository::getCurrentSchoolYear()->getKey())
            );
        });
    }

    public static function getTeacherUsersForSchoolLocation(SchoolLocation $schoolLocation)
    {
        return User::distinct()
            ->select('users.*')
            ->join('teachers', 'users.id', '=', 'teachers.user_id')
            ->leftJoin('user_roles', 'users.id', '=', 'user_roles.user_id')
            ->whereIn(
                'users.id',
                DB::table('school_location_user')
                    ->select('user_id')
                    ->where('school_location_id', $schoolLocation->getKey())
            )
            ->where('user_roles.role_id', Role::TEACHER)
            ->where('users.is_examcoordinator', 0);
    }
}

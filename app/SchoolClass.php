<?php namespace tcCore;

use Carbon\Carbon;
use Closure;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Jobs\PValues\UpdatePValueSchoolClass;
use tcCore\Jobs\Rating\UpdateRatingsSchoolClass;
use tcCore\Lib\Models\AccessCheckable;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\Lib\User\Roles;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use tcCore\Traits\UuidTrait;

class SchoolClass extends BaseModel implements AccessCheckable
{

    use SoftDeletes;
    use UuidTrait;

    protected $casts = [
        'uuid'       => EfficientUuid::class,
        'visible'    => 'boolean',
        'deleted_at' => 'datetime',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'school_classes';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'created_by', 'old_school_class_id', 'school_location_id', 'subject_id', 'education_level_id', 'school_year_id',
        'name', 'education_level_year', 'is_main_school_class', 'do_not_overwrite_from_interface', 'demo', 'visible',
        'guest_class', 'test_take_id'
    ];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $students;
    protected $mentors;
    protected $managers;

    public function fill(array $attributes)
    {
        parent::fill($attributes);

        if (array_key_exists('demo_restriction_overrule', $attributes)) {
            $this->demoRestrictionOverrule = true;
        }

        if (array_key_exists('student_users', $attributes)) {
            $this->students = $attributes['student_users'];
        } elseif (array_key_exists('add_student_user', $attributes) || array_key_exists('delete_student_user',
                $attributes)) {
            $this->students = $this->students()->withTrashed()->pluck('user_id')->all();
            if (array_key_exists('add_student_user', $attributes)) {
                array_push($this->students, $attributes['add_student_user']);
            }

            if (array_key_exists('delete_student_user', $attributes)) {
                if (($key = array_search($attributes['delete_student_user'], $this->students)) !== false) {
                    unset($this->students[$key]);
                }
            }
        }

        if (array_key_exists('mentor_users', $attributes)) {
            $this->mentors = $attributes['mentors'];
        } elseif (array_key_exists('add_mentor_user', $attributes) || array_key_exists('delete_mentor_user',
                $attributes)) {
            $this->mentors = $this->mentors()->withTrashed()->pluck('user_id')->all();
            if (array_key_exists('add_mentor_user', $attributes)) {
                array_push($this->mentors, $attributes['add_mentor_user']);
            }

            if (array_key_exists('delete_mentor_user', $attributes)) {
                if (($key = array_search($attributes['delete_mentor_user'], $this->mentors)) !== false) {
                    unset($this->mentors[$key]);
                }
            }
        }

        if (array_key_exists('manager_users', $attributes)) {
            $this->managers = $attributes['manager_users'];
        } elseif (array_key_exists('add_manager_user', $attributes) || array_key_exists('delete_manager_user',
                $attributes)) {
            $this->managers = $this->managers()->withTrashed()->pluck('user_id')->all();
            if (array_key_exists('add_manager_user', $attributes)) {
                array_push($this->managers, $attributes['add_manager_user']);
            }

            if (array_key_exists('delete_manager_user', $attributes)) {
                if (($key = array_search($attributes['delete_manager_user'], $this->managers)) !== false) {
                    unset($this->managers[$key]);
                }
            }
        }
    }

    public static function boot()
    {
        parent::boot();
// column for scope is not available before 19-6-2021 this is for now solved by not adding the scope for running migrations;
        if (!BaseHelper::isRunningTestRefreshDb()) {
            static::addGlobalScope('visibleOnly', function (Builder $builder) {
                $builder->where('visible', 1);
            });
        }

        self::creating(function (SchoolClass $schoolClass) {
            self::setDoNotOverwriteFromInterfaceOnDemoClass($schoolClass);
        });

        // Progress additional answers
        static::updated(function (SchoolClass $schoolClass) {
            if ($schoolClass->getOriginal('education_level_id') != $schoolClass->getAttribute('education_level_id') || $schoolClass->getOriginal('education_level_year') != $schoolClass->getAttribute('education_level_year')) {
                Queue::push(new UpdatePValueSchoolClass($schoolClass));
                Queue::push(new UpdateRatingsSchoolClass($schoolClass));
            }
        });

        static::saved(function (SchoolClass $schoolClass) {
            if ($schoolClass->students !== null) {
                $schoolClass->saveStudents();
            }
            if ($schoolClass->mentors !== null) {
                $schoolClass->saveMentors();
            }
            if ($schoolClass->managers !== null) {
                $schoolClass->saveManagers();
            }
        });

        static::updating(function (SchoolClass $schoolClass) {
            if ($schoolClass->getOriginal('demo') == true && !isset($schoolClass->demoRestrictionOverrule)) {
                return false;
            }
            if (isset($schoolClass->demoRestrictionOverrule)) {
                unset($schoolClass->demoRestrictionOverrule);
            }
            self::setDoNotOverwriteFromInterfaceOnDemoClass($schoolClass);
        });


        static::deleting(function (SchoolClass $schoolClass) {
            if ($schoolClass->getOriginal('demo') == true) {
                return false;
            }
        });

        static::deleted(function (SchoolClass $schoolClass) {
            $managers = Manager::where('school_class_id', $schoolClass->getKey())->get();
            $managers->each(function (Manager $manager) {
                $manager->delete();
            });
            $mentors = Mentor::where('school_class_id', $schoolClass->getKey())->get();
            $mentors->each(function (Mentor $mentor) {
                $mentor->delete();
            });
            $teachers = Teacher::where('class_id', $schoolClass->getKey())->get();
            $teachers->each(function (Teacher $teacher) {
                $teacher->delete();
            });
            $students = Student::where('class_id', $schoolClass->getKey())->get();
            $students->each(function (Student $student) {
                $student->delete();
            });
        });
    }

    public function schoolLocation()
    {
        return $this->belongsTo('tcCore\SchoolLocation');
    }


    public function mentors()
    {
        return $this->hasMany('tcCore\Mentor');
    }

    public function mentorUsers()
    {
        return $this->belongsToMany('tcCore\User', 'mentors', 'school_class_id', 'user_id')
            ->withTrashed()
            ->withPivot([
                $this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()
            ])->wherePivot($this->getDeletedAtColumn(), null);
    }

    protected function saveMentors()
    {
        $mentors = $this->mentorUsers()->withTrashed()->get();
        $this->syncTcRelation($mentors, $this->mentors, 'user_id', function ($schoolClass, $userId) {
            Mentor::create(['user_id' => $userId, 'school_class_id' => $schoolClass->getKey()]);
        });

        $this->mentors = null;
    }

    public function managers()
    {
        return $this->hasMany('tcCore\Manager');
    }

    public function managerUsers()
    {
        return $this->belongsToMany('tcCore\User', 'managers', 'school_class_id', 'user_id')->withPivot([
            $this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()
        ])->wherePivot($this->getDeletedAtColumn(), null);
    }

    protected function saveManagers()
    {
        $managers = $this->managerUsers()->withTrashed()->get();
        $this->syncTcRelation($managers, $this->managers, 'user_id', function ($schoolClass, $userId) {
            Manager::create(['user_id' => $userId, 'school_class_id' => $schoolClass->getKey()]);
        });

        $this->managers = null;
    }

    public function students()
    {
        return $this->hasMany('tcCore\Student', 'class_id');
    }

    public function studentUsers()
    {
        return $this->belongsToMany('tcCore\User', 'students', 'class_id', 'user_id')
            ->withPivot([
                $this->getCreatedAtColumn(),
                $this->getUpdatedAtColumn(),
                $this->getDeletedAtColumn()
            ])
            ->wherePivot($this->getDeletedAtColumn(), null);
    }

    protected function saveStudents()
    {
        $students = $this->studentUsers()->withTrashed()->get();
        $this->syncTcRelation($students, $this->students, 'user_id', function ($schoolClass, $userId) {
            Student::create(['user_id' => $userId, 'class_id' => $schoolClass->getKey()]);
        });
        $this->students = null;
    }

    public function educationLevel()
    {
        return $this->belongsTo('tcCore\EducationLevel');
    }

    public function schoolYear()
    {
        return $this->belongsTo('tcCore\SchoolYear');
    }

    public function teacherUsers()
    {
        return $this->belongsToMany('tcCore\User', 'teachers', 'class_id', 'user_id')
            ->withTrashed()
            ->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()])
            ->wherePivot($this->getDeletedAtColumn(), null);
    }

    public function teacher()
    {
        return $this->hasMany('tcCore\Teacher', 'class_id');
    }

    public function pValue()
    {
        return $this->hasMany('tcCore\PValue');
    }

    public function ratings()
    {
        return $this->hasMany('tcCore\Rating');
    }

    public function averageRatings()
    {
        return $this->hasMany('tcCore\AverageRating');
    }

    public function importLog()
    {
        return $this->hasOne('tcCore\SchoolClassImportLog', 'class_id', 'id');
    }

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        $roles = Roles::getUserRoles();
        $user = auth()->user();
        if (!in_array('Administrator', $roles)) {
            $query->where(function ($query) use ($roles, $user) {
                $userId = $user->getKey();

                if (in_array('Account manager', $roles) || in_array('School manager', $roles)) {
                    $query->orWhereIn('school_location_id', function ($query) {
                        $query->select('id')->from(with(new SchoolLocation())->getTable())->whereNull('deleted_at');
                        with(new SchoolLocation())->scopeFiltered($query);
                    });
                }

                if (in_array('Teacher', $roles)) {
                    if ($user->isValidExamCoordinator()) {
                        $this->filterForExamcoordinator($query, $user);
                    } else {
                        $query->orWhereIn($this->getTable().'.id', function ($query) use ($userId) {
                            $query->select('class_id')->from(with(new Teacher())->getTable())->whereNull('deleted_at');
                            $query->where('user_id', $userId);
                        });
                    }

                }

                if (in_array('Mentor', $roles)) {
                    $query->orWhereIn($this->getTable().'.id', function ($query) use ($userId) {
                        $query->select('school_class_id')->from(with(new Mentor())->getTable())->whereNull('deleted_at')->where('user_id',
                            $userId);
                    });
                }

                if (in_array('School management', $roles)) {
                    $query->orWhereIn($this->getTable().'.id', function ($query) use ($userId) {
                        $query->select('school_class_id')->from(with(new Manager())->getTable())->whereNull('deleted_at')->where('user_id',
                            $userId);
                    });
                }

                if (in_array('Student', $roles)) {
                    $query->orWhereIn($this->getTable().'.id', function ($query) use ($userId) {
                        $query->select('class_id')->from(with(new Student())->getTable())->whereNull('deleted_at')->where('user_id',
                            $userId);
                    });
                }
            });
        }

        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'name':
                    $query->where('name', 'LIKE', '%'.$value.'%');
                    break;
                case 'current_school_year':
                    if ($value != true) {
                        break;
                    }

                    $schoolYearRepository = new SchoolYearRepository();
                    $currentSchoolYear = $schoolYearRepository->getCurrentOrPreviousSchoolYear();

                    if ($currentSchoolYear instanceof SchoolYear) {
                        $query->where('school_year_id', '=', $currentSchoolYear->getKey());
                    }
                    break;
                case 'current':
                    if ($value != true) {
                        break;
                    }
                    $schoolYearRepository = new SchoolYearRepository();
                    $currentSchoolYears = $schoolYearRepository->getCurrentSchoolYears();
                    $query->whereIn('school_year_id', $currentSchoolYears->map->getKey());
                    break;
                case 'school_year_id':
                    if (is_array($value)) {
                        $query->whereIn('school_year_id', $value);
                    } else {
                        $query->where('school_year_id', '=', $value);
                    }
                    break;
                case 'school_location_id':
                    if (is_array($value)) {
                        $query->whereIn('school_location_id', $value);
                    } else {
                        $query->where('school_location_id', '=', $value);
                    }
                    break;
                case 'is_main_school_class':
                    $query->where('is_main_school_class', '=', $value);
                    break;
                case 'demo':
                    $query->where('demo', '=', $value);
                    break;
                case 'without_guest_classes':
                    $query->withoutGuestClasses();
                    break;
                case 'subject_id':
                    $query->whereIn('id',
                        DB::table('school_classes as sc2')
                            ->select('sc2.id')
                            ->join('teachers', 'teachers.class_id', '=', 'sc2.id')
                            ->where('teachers.subject_id', $value)
                            ->whereNull('teachers.deleted_at')
                    );
                    break;
                case 'base_subject_id':
                    $query->whereIn('id',
                        DB::table('school_classes as sc2')
                            ->select('sc2.id')
                            ->join('teachers', 'teachers.class_id', '=', 'sc2.id')
                            ->whereIn(
                                'teachers.subject_id',
                                Subject::select('id')->whereBaseSubjectId($value)
                            )
                            ->whereNull('teachers.deleted_at')
                    );
                    break;
                default:
                    break;
            }
        }

        if ($sorting === '' || $sorting === []) {
            $sorting = ['name' => 'asc'];
        }
        foreach ($sorting as $key => $value) {
            switch (strtolower($value)) {
                case 'id':
                case 'name':
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
                case 'name':
                case 'school_location_id':
                    $query->orderBy($key, $value);
                    break;
            }
        }

        return $query;
    }

    public function canAccess()
    {
        $roles = Roles::getUserRoles();

        if (in_array('Administrator', $roles)) {
            return true;
        }

        if ((in_array('Account manager', $roles) || in_array('School manager',
                    $roles)) && SchoolLocation::filtered(['id' => $this->getAttribute('school_location_id')])->count() > 0) {
            return true;
        }

        $userId = Auth::user()->getKey();
        if (in_array('Teacher', $roles) && Teacher::where('user_id', $userId)->where('class_id',
                $this->getKey())->count() > 0) {
            return true;
        }

        if (in_array('Mentor', $roles) && Mentor::where('user_id', $userId)->where('school_class_id',
                $this->getKey())->count() > 0) {
            return true;
        }

        if (in_array('School management', $roles) && Manager::where('user_id', $userId)->where('school_class_id',
                $this->getKey())->count() > 0) {
            return true;
        }

        if (in_array('Student', $roles) && Student::where('user_id', $userId)->where('class_id',
                $this->getKey())->count() > 0) {
            return true;
        }

        return false;
    }

    public function canAccessBoundResource($request, Closure $next)
    {
        return $this->canAccess();
    }

    public function getAccessDeniedResponse($request, Closure $next)
    {
        throw new AccessDeniedHttpException('Access to school class denied');
    }

    private static function setDoNotOverwriteFromInterfaceOnDemoClass($schoolClass)
    {
        if ($schoolClass->demo == true) {
            $schoolClass->do_not_overwrite_from_interface = true;
        }
    }

    public static function getAllClassesForSchoolLocation($schoolLocationId, $order)
    {
        $currentYear = SchoolYearRepository::getCurrentSchoolYear();

        return SchoolClass::where('school_location_id', $schoolLocationId)
            ->when($currentYear instanceof SchoolYear, function ($query) use ($currentYear) {
                $query->where('school_year_id', $currentYear->getKey());
            })
            ->when(!empty($order), function ($query) use ($order) {
                collect($order)->each(function ($direction, $key) use ($query) {
                    $query->orderBy($key, $direction);
                });
            })
            ->withoutGuestClasses()
            ->paginate(15);
    }

    public static function createGuestClassForTestTake(TestTake $testTake)
    {
        $schoolYear = SchoolYearRepository::getCurrentSchoolYear();
        $testTake->load('test:id,education_level_id,education_level_year');

        return SchoolClass::create([
            'name'                 => 'guest_class_'.$testTake->getKey(),
            'education_level_id'   => $testTake->test->education_level_id,
            'education_level_year' => $testTake->test->education_level_year,
            'school_location_id'   => $testTake->user()->value('school_location_id'),
            'school_year_id'       => $schoolYear->getKey(),
            'guest_class'          => true,
            'test_take_id'         => $testTake->getKey(),
            'is_main_school_class' => 0,
        ]);
    }

    public function getNameAttribute($value)
    {
        if (Str::contains($value, 'guest_class')) {
            return __('school_classes.guest_accounts');
        }
        return $value;
    }

    public function scopeWithoutGuestClasses($query)
    {
        return $query->where('guest_class', 0);
    }

    public function scopeWithGuestClasses($query)
    {
        return $query->where('guest_class', 1);
    }

    private function filterForExamcoordinator($query, User $user)
    {
        $classIds = SchoolClass::select(['id'])->where('school_location_id',
            $user->school_location_id)->withoutGuestClasses();

        return $query->orWhereIn(self::getTable().'.id', $classIds)->where('demo', 0);
    }

    public function scopeFromTestTakes($query, $testTakeIds)
    {
        return $query->whereIn(
            'school_classes.id',
            TestParticipant::select('school_class_id')
                ->when(
                    is_int($testTakeIds),
                    fn($query) => $query->where('test_take_id', $testTakeIds),
                    fn($query) => $query->whereIn('test_take_id', collect($testTakeIds))
                )
        )
            ->withTrashed()
            ->distinct();
    }
}

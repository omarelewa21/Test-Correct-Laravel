<?php namespace tcCore;

use iio\libmergepdf\Exception;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use tcCore\Traits\UuidTrait;

class EducationLevel extends BaseModel
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
    protected $table = 'education_levels';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'max_years'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $schoolLocations;

    public function schoolClasses()
    {
        return $this->hasMany('tcCore\SchoolClass');
    }

    public function tests()
    {
        return $this->hasMany('tcCore\Test');
    }

    public function attainments()
    {
        return $this->hasMany('tcCore\Attainment', null, 'attainment_education_level_id');
    }

    public function pValue()
    {
        return $this->hasMany('tcCore\PValue');
    }

    public function ratings()
    {
        return $this->hasMany('tcCore\Rating');
    }

    public static function boot()
    {
        parent::boot();

        // Progress additional answers
        static::saved(function (EducationLevel $educationLevel) {
            if ($educationLevel->schoolLocations !== null) {
                $educationLevel->saveSchoolLocations();
            }

            $educationLevel->refresh();
            if (null === $educationLevel->attainment_education_level_id) {
                $educationLevel->attainment_education_level_id = $educationLevel->getKey();
                $educationLevel->save();
            }
        });
    }

    protected function saveSchoolLocations()
    {
        $schoolLocationEducationLevels = $this->schoolLocationEducationLevels()->withTrashed()->get();
        $this->syncTcRelation($schoolLocationEducationLevels, $this->schoolLocations, 'school_location_id',
            function ($educationLevel, $schoolLocationId) {
                SchoolLocationEducationLevel::create([
                    'education_level_id' => $educationLevel->getKey(), 'school_location_id' => $schoolLocationId
                ]);
            });

        $this->schoolLocations = null;
    }

    public function schoolLocationEducationLevels()
    {
        return $this->hasMany('tcCore\SchoolLocationEducationLevel');
    }

    public function schoolLocations()
    {
        return $this->belongsToMany('tcCore\SchoolLocation', 'school_location_education_levels', 'education_level_id',
            'school_location_id')->withPivot([
            $this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()
        ])->wherePivot($this->getDeletedAtColumn(), null);
    }

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        $user = auth()->user();
        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'user_id':
                    if ($user->isValidExamCoordinator()) {
                        $this->filterForExamcoordinator($query, $user);
                    } else {
                        $query->whereIn('id', function ($query) use ($value) {
                            $query->select('education_level_id')
                                ->from(with(new SchoolClass())->getTable())
                                ->whereIn('id', function ($query) use ($value) {
                                    $query->select('class_id')
                                        ->from(with(new Teacher())->getTable())
                                        ->where('deleted_at', null);
                                    if (is_array($value)) {
                                        $query->whereIn('user_id', $value);
                                    } else {
                                        $query->where('user_id', '=', $value);
                                    }
                                })
                                ->where('deleted_at', null);
                        });
                    }

                    break;
                case 'school_location_id':
                    $query->whereIn(
                        'id',
                        SchoolLocationEducationLevel::whereIn(
                            'school_location_id',
                            Arr::wrap($value)
                        )
                            ->select('education_level_id')
                    );
                    break;
            }
        }

        //Todo: More sorting
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
                    $query->orderBy($key, $value);
                    break;
            }

        }

        return $query;
    }

    public static function yearsForStudent(User $student)
    {
        return $student->studentSchoolClasses()->pluck('education_level_year')->unique();
    }

    public static function getLatestEducationLevelAndEducationLevelYearForStudent(User $student)
    {
        $latestSchoolClassForStudent = $student->studentSchoolClasses()
            ->whereId(
                DB::table('students')
                    ->where('user_id', $student->id)
                    ->orderByDesc('created_at')
                    ->whereNull('deleted_at')
                    ->value('class_id')
            )->with('educationLevel')
            ->first();
        $min = 1;
        if ($latestSchoolClassForStudent->educationLevel->min_attainment_year <= $latestSchoolClassForStudent->education_level_year) {
            $min = $latestSchoolClassForStudent->educationLevel->min_attainment_year;
        }
        return [
            'education_level_id'    => $latestSchoolClassForStudent->education_level_id,
            'education_level_years' => collect(range($min, $latestSchoolClassForStudent->education_level_year)),
        ];
    }

    public static function getStartAndEndDateForLatestEducationLevelForStudent(User $student)
    {
        $result = $student->studentSchoolClasses()->where(
            'education_level_id',
            self::getLatestEducationLevelAndEducationLevelYearForStudent($student)['education_level_id']
        )->join('periods', 'school_classes.school_year_id', 'periods.school_year_id')
            ->selectRaw('min(periods.start_date) as start_date, max(periods.end_date) as end_date')
            ->first()
            ->toArray();

        return [
            'start_date' => $result['start_date'],
            'end_date'   => $result['end_date'],
        ];
    }


    private function filterForExamcoordinator($query, User $user)
    {
        return $query->whereIn(
            'id',
            $user->schoolLocation->schoolClasses()->select('education_level_id')
        );
    }

    public static function getAttainmentType(User $user)
    {
        return $user->studentSchoolClasses()->get()
            ->map(function ($schoolClass) {
                return (object)[
                    'educationLevel' => $schoolClass->educationLevel->name,
                    'attainmentType' => $schoolClass->educationLevel->getType($schoolClass),
                ];
            })->sortBy(fn($item) => $item->attainmentType == LearningGoal::TYPE ? 1 : 0)
            ->first()
            ->attainmentType;
    }

    private function getType($schoolClass)
    {
        return $this->min_attainment_year <= $schoolClass->education_level_year ? Attainment::TYPE : LearningGoal::TYPE;
    }

    public static function getLatestForStudentWithSubject(User $user, Subject $subject)
    {
        if (!$user->isA('student')) {
            throw new \ErrorException('method can only be called as a student');
        }
        $class = $user->studentSchoolClasses()
            ->select('school_classes.*')
            ->join('teachers', 'school_classes.id', '=', 'teachers.class_id')
            ->where('teachers.subject_id', $subject->getKey())
            ->orderBy('school_classes.created_at', 'desc')
            ->limit(1)
            ->first();
        if (!$class) {
            throw new \ErrorException('no school_class found for provided student and subject');
        }

        return $class->educationLevel;
    }
}

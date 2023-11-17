<?php namespace tcCore;

use Closure;
use Dyrynda\Database\Casts\EfficientUuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use tcCore\Http\Helpers\AnalysesGeneralDataHelper;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Lib\Models\AccessCheckable;
use tcCore\Lib\Models\BaseModel;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\Lib\User\Roles;
use tcCore\Traits\UuidTrait;

class Subject extends BaseModel implements AccessCheckable
{

    use SoftDeletes;
    use UuidTrait;

    const NOT_ALLOWED_FOR_TEACHER_EXCEPTION_MSG = 'Not allowed For teacher';
    protected $casts = [
        'uuid'       => EfficientUuid::class,
        'deleted_at' => 'datetime',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'subjects';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'abbreviation', 'section_id', 'base_subject_id', 'demo'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $schoolLocations;

    public function baseSubject()
    {
        return $this->belongsTo('tcCore\BaseSubject');
    }

    public function section()
    {
        return $this->belongsTo('tcCore\Section');
    }

    public function teachers()
    {
        return $this->hasMany('tcCore\Teacher');
    }

    public function questions()
    {
        return $this->hasMany('tcCore\Question');
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

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        $roles = Roles::getUserRoles();
        $user = Auth::user();

        if (!in_array('Administrator', $roles) && !$user->isPartOfSharedSection()) {
            $query->whereIn('section_id', function ($query) {
                $query->select('id')
                    ->from(with(new Section())->getTable())
                    ->whereNull('deleted_at');
                with(new Section())->scopeFiltered($query);
            });
        } elseif (!in_array('Administrator', $roles) && $user->isPartOfSharedSection()) {
            $query->whereIn('section_id',
                $user->sections()->pluck('id')
                    ->union(
                        $user->sectionsOnlyShared()->pluck('id')
                    )
            );
        }

        $subject = (new DemoHelper())->getDemoSectionForSchoolLocation($user->getAttribute('school_location_id'));
        if (!is_null($subject)) {
            $query->where(function ($q) use ($subject) {
                $q->where(function ($query) use ($subject) {
                    $query->where('demo', false);
                })->orWhere('id', $subject->getKey());
            });
        }

        foreach ($filters as $key => $value) {
            switch ($key) {
                case 'user_id':
                    if ($user->isValidExamCoordinator()) {
                        $this->filterForExamcoordinator($query, $user);
                    } else {
                        $query->whereIn('id', function ($query) use ($value) {
                            $query->select('subject_id')
                                ->from(with(new Teacher())->getTable())
                                ->whereNull('deleted_at');
                            if (is_array($value)) {
                                $query->whereIn('user_id', $value);
                            } else {
                                $query->where('user_id', '=', $value);
                            }
                        });
                    }

                    break;
                case 'demo' :
                    $query->where('demo', $value);
                    break;
                case 'imp' :
                    if ($value == 0) {
                        $query->where('abbreviation', '<>', 'imp');
                    }
                    break;
                case 'user_current':
                    if ($user->isValidExamCoordinator()) {
                        $this->filterForExamcoordinator($query, $user);
                    } else {
                        $schoolYears = SchoolYearRepository::getCurrentSchoolYears();
                        $query->whereIn('id', function ($query) use ($value, $schoolYears, $user) {
                            $query->select('subject_id')
                                ->from(with(new Teacher())->getTable())
                                ->whereNull('teachers.deleted_at')
                                ->leftJoin('school_classes', 'school_classes.id', '=', 'teachers.class_id')
                                ->whereIn('school_classes.school_year_id', $schoolYears->map->getKey());
                            if (is_array($value)) {
                                $query->whereIn('user_id', $value);
                            } else {
                                $query->where('user_id', '=', $value);
                            }
                        }
                        );
                    }
                    break;
                case 'show_in_onboarding' :
                    $query->whereNotIn('base_subject_id', function ($query) use ($value) {
                        $query->select('id')
                            ->from(with(new BaseSubject())->getTable())
                            ->where('show_in_onboarding', $value);
                    });
                    break;
                case 'base_subject_id' :
                    $query->where('base_subject_id', $value);
                    break;
            }
        }

        //Todo: More sorting
        foreach ($sorting as $key => $value) {
            switch (strtolower($value)) {
                case 'id':
                case 'name':
                case 'abbreviation':
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
                case 'abbreviation':
                    $query->orderBy($key, $value);
                    break;
            }

        }

        return $query;
    }

    public function scopeFilterForStudent($query, User $user)
    {
        if (!$user->isA('student')) {
            throw new \Exception(self::NOT_ALLOWED_FOR_TEACHER_EXCEPTION_MSG);
        }

        $subQuery = Teacher::select('subject_id')
            ->whereIn(
                'class_id',
                Student::select('class_id')->where('user_id', $user->getKey()
                )
            );

        return $query->whereIn('id', $subQuery);
    }

    public function scopeFilterForStudentCurrentSchoolYear($query, User $user)
    {
        if (!$user->isA('student')) {
            throw new \Exception(self::NOT_ALLOWED_FOR_TEACHER_EXCEPTION_MSG);
        }

        $subQuery = [];

        if ($currentSchoolYear = SchoolYearRepository::getCurrentSchoolYear()) {
            $subQuery =
                SchoolClass::select('subject_id')
                    ->join('teachers', 'school_classes.id', '=', 'teachers.class_id')
                    ->join('students', 'school_classes.id', '=', 'students.class_id')
                    ->where('students.user_id', $user->getKey())
                    ->where('school_year_id', $currentSchoolYear->getKey());
        }

        return $query->whereIn('id', $subQuery);
    }

    public function scopeCitoFiltered($query, $filters = [], $sorting = [])
    {
        $citoSchool = SchoolLocation::where('customer_code', 'CITO-TOETSENOPMAAT')->first();

        return $this->filterByUserAndSchoolLocation($query, Auth::user(), $citoSchool);
    }

    public function scopeExamFiltered($query, $filters = [], $sorting = [])
    {
        $examSchool = SchoolLocation::where('customer_code', config('custom.examschool_customercode'))->first();

        return $this->filterByUserAndSchoolLocation($query, Auth::user(), $examSchool);
    }

    public function scopeNationalItemBankFiltered($query, $filters = [], $sorting = [])
    {
        $nationalItemBankSchools = collect([
            SchoolLocation::where('customer_code', config('custom.national_item_bank_school_customercode'))->first(),
            SchoolLocation::where('customer_code', config('custom.examschool_customercode'))->first(),
           // SchoolLocation::where('customer_code', 'CITO-TOETSENOPMAAT')->first(),
        ])->filter()->all();

        return $this->filterByUserAndSchoolLocation($query, Auth::user(), $nationalItemBankSchools);
    }

    public function scopeThiemeMeulenhoffFiltered($query, $filters = [], $sorting = [])
    {
        $schoolLocation = SchoolLocation::where('customer_code', config('custom.thieme_meulenhoff_school_customercode'))->first();

        return $this->filterByUserAndSchoolLocation($query, Auth::user(), $schoolLocation);
    }
    public function scopeCreathlonFiltered($query, $filters = [], $sorting = [])
    {
        $creathlonSchoolLocation = SchoolLocation::where('customer_code', config('custom.creathlon_school_customercode'))->first();

        return $this->filterByUserAndSchoolLocation($query, Auth::user(), $creathlonSchoolLocation);
    }
    public function scopeFormidableFiltered($query, $filters = [], $sorting = [])
    {
        $formidableSchoolLocation = SchoolLocation::where('customer_code', config('custom.formidable_school_customercode'))->first();

        return $this->filterByUserAndSchoolLocation($query, Auth::user(), $formidableSchoolLocation);
    }

    public function scopeOlympiadeFiltered($query, $filters = [], $sorting = [])
    {
        $olympiadeSchoolLocation = SchoolLocation::where('customer_code', config('custom.olympiade_school_customercode'))->first();

        return $this->filterByUserAndSchoolLocation($query, Auth::user(), $olympiadeSchoolLocation);
    }

    public function scopeOlympiadeArchiveFiltered($query, $filters = [], $sorting = [])
    {
        $olympiadeArchiveSchoolLocation = SchoolLocation::where('customer_code', config('custom.olympiade_archive_school_customercode'))->first();

        return $this->filterByUserAndSchoolLocation($query, Auth::user(), $olympiadeArchiveSchoolLocation);
    }

    private function filterByUserAndSchoolLocation($query, User $user, $schoolLocations)
    {
        if (!$schoolLocations) { // slower but as a fallback in case there's no cito school
            $query->where('subjects.id', -1);
            return $query;
        }
        $schoolLocations = array_filter(
            Arr::wrap($schoolLocations)
        );

        $subjectIds = [];

        foreach ($schoolLocations as $schoolLocation) {
            $subjectIds = array_merge($subjectIds, $this->getAvailableSubjectsForSchoolLocation($schoolLocation)
                ->whereIn('base_subject_id', BaseSubject::getIdsForUserInCurrentSchoolLocation($user))
                ->pluck('id')
                ->unique()
                ->toArray()
            );
        }

        $query->whereIn('id', $subjectIds);
        return $query;
    }

    private function filterForExamcoordinator($query, User $user)
    {
        $subjectIds = $user->schoolLocation->schoolLocationSections()
            ->join('sections', 'school_location_sections.section_id', 'sections.id')
            ->join('subjects', 'subjects.section_id', 'sections.id')
            ->select('subjects.id');

        return $query->whereIn('id', $subjectIds)->where('demo', 0);
    }

    private function getAvailableSubjectsForSchoolLocation(SchoolLocation $schoolLocation)
    {
        return
            $this->select('subjects.*')
            ->join('sections', 'sections.id', '=', 'subjects.section_id')
            ->join('school_location_sections', 'sections.id', '=', 'school_location_sections.section_id')
            ->where('school_location_sections.school_location_id', '=', $schoolLocation->getKey())
            ->get();
    }

    public function canAccess()
    {
        $roles = Roles::getUserRoles();
        if (in_array('Administrator', $roles)) {
            return true;
        }

        return Section::filtered(['id' => $this->getAttribute('section_id')])->count() > 0;
    }

    public function canAccessBoundResource($request, Closure $next)
    {
        return $this->canAccess();
    }

    public function getAccessDeniedResponse($request, Closure $next)
    {
        throw new AccessDeniedHttpException('Access to subject denied');
    }

    public static function getSubjectIdsOfSchoolLocationByCustomerCodesAndUser($customerCodes, User $user): array
    {
        $userBaseSubjectIds = BaseSubject::getIdsForUserInCurrentSchoolLocation($user);

        return SchoolLocation::whereIn('school_locations.customer_code', Arr::wrap($customerCodes))
            ->join('school_location_sections', 'school_locations.id', '=', 'school_location_sections.school_location_id')
            ->join('sections', 'school_location_sections.section_id', '=', 'sections.id')
            ->join('subjects', 'subjects.section_id', '=', 'sections.id')
            ->whereIn('subjects.base_subject_id', $userBaseSubjectIds)
            ->distinct()
            ->pluck('subjects.id')->toArray();
    }

    public static function boot()
    {
        parent::boot();

        static::updating(function (self $item) {
            if ($item->getOriginal('demo') == true) {
                return false;
            }
        });

        static::deleting(function (self $item) {
            if ($item->getOriginal('demo') == true) {
                return false;
            }
        });
    }

    public function scopeAllowedSubjectsByBaseSubjectForUser($query, BaseSubject $baseSubject, User $forUser)
    {
        return $query->filtered(['base_subject_id' => $baseSubject->id, 'user_id' => $forUser->id], []);
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }

    public function scopeFromTests($query, $testIds)
    {
        return $query->whereIn(
            'id',
            Test::select('subject_id')->whereIn('id', collect($testIds))
        )
            ->distinct();
    }

    public static function getIdsForContentSource(User $user, array $customer_codes)
    {
        if ($user->isValidExamCoordinator()) {
            //This returns a queryBuilder for efficiency purposes.
            return Subject::select('id')->whereIn('base_subject_id', BaseSubject::select('id')->distinct());
        }

        return Subject::getSubjectIdsOfSchoolLocationByCustomerCodesAndUser(Arr::wrap($customer_codes), $user);
    }

    public static function getIdsForSharedSections(User $user)
    {
        if ($user->schoolLocation->sharedSections()->exists()) {
            $sharedSectionIdsQuery = $user->schoolLocation->sharedSections()->select(['id']);
            $baseSubjectIdsQuery = $user->subjects()->select(['base_subject_id']);

            return Subject::select(['id'])
                ->whereIn(
                    'section_id',
                    $sharedSectionIdsQuery
                )
                ->when(!$user->isValidExamCoordinator(), function ($query) use ($baseSubjectIdsQuery) {
                    $query->whereIn('base_subject_id', $baseSubjectIdsQuery)->pluck('id')->unique();
                });
        }

        return false;
    }
}

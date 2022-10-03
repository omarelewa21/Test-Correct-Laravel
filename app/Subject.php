<?php namespace tcCore;

use Closure;
use Illuminate\Support\Arr;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Lib\Models\AccessCheckable;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\Lib\User\Roles;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use tcCore\Traits\UuidTrait;
use Illuminate\Support\Facades\Auth;

class Subject extends BaseModel implements AccessCheckable
{

    use SoftDeletes;
    use UuidTrait;

    const NOT_ALLOWED_FOR_TEACHER_EXCEPTION_MSG = 'Not allowed For teacher';
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
                $user->sections()
                    ->union(
                        $user->sectionsOnlyShared()
                    )->pluck('id')
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
                    if( $user->isValidExamCoordinator() ){
                        $this->filterForExamcoordinator($query, $user);
                    }else{
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
                    if( $user->isValidExamCoordinator() ){
                        $this->filterForExamcoordinator($query, $user);
                    }else{
                        $schoolYear = SchoolYearRepository::getCurrentSchoolYear();
                        $query->whereIn('id', function ($query) use ($value, $schoolYear, $user) {
                                $query->select('subject_id')
                                ->from(with(new Teacher())->getTable())
                                ->whereNull('teachers.deleted_at')
                                ->leftJoin('school_classes', 'school_classes.id', '=', 'teachers.class_id')
                                ->where('school_classes.school_year_id', $schoolYear->getKey());
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

    public function scopeFilterForStudent($query, User $user) {
        if (!$user->isA('student')){
            throw new \Exception(self::NOT_ALLOWED_FOR_TEACHER_EXCEPTION_MSG);
        }

        $subQuery = Teacher::select('subject_id')->whereIn('class_id', Student::select('class_id')->where('user_id', $user->getKey()));

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
        $nationalItemBankSchools = [
            SchoolLocation::where('customer_code', config('custom.national_item_bank_school_customercode'))->first(),
            SchoolLocation::where('customer_code', config('custom.examschool_customercode'))->first(),
//            SchoolLocation::where('customer_code', 'CITO-TOETSENOPMAAT')->first(),
        ];

        return $this->filterByUserAndSchoolLocation($query, Auth::user(), $nationalItemBankSchools);
    }

    public function scopeCreathlonFiltered($query, $filters = [], $sorting = [])
    {
        $creathlonSchoolLocation = SchoolLocation::where('customer_code', config('custom.creathlon_school_customercode'))->first();

        return $this->filterByUserAndSchoolLocation($query, Auth::user(), $creathlonSchoolLocation);
    }

    private function filterByUserAndSchoolLocation($query, User $user, $schoolLocations)
    {
        if (!$schoolLocations) { // slower but as a fallback in case there's no cito school
            $query->where('subjects.id', -1);
            return $query;
        }

        $schoolLocations = Arr::wrap($schoolLocations);

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
        switch ($user->is_examcoordinator_for) {
            case 'SCHOOL_LOCATION':
                $subjectIds = $user->schoolLocation->schoolLocationSections()
                                ->join('sections', 'school_location_sections.section_id', 'sections.id')
                                ->join('subjects', 'subjects.section_id', 'sections.id')
                                ->select('subjects.id', 'subjects.name')->groupBy('subjects.name')->pluck('id')->toArray();
                break;
            case 'SCHOOL':
                $subjectIds = $user->schoolLocation->school->schoolLocations()
                                ->join('school_location_sections', 'school_location_sections.school_location_id', 'school_locations.id')
                                ->join('sections', 'school_location_sections.section_id', 'sections.id')
                                ->join('subjects', 'subjects.section_id', 'sections.id')
                                ->select('subjects.id', 'subjects.name')->groupBy('subjects.name')->pluck('id')->toArray();
                break;
            default:
                $subjectIds = [];
                break;
        }
        return $query->whereIn('id', $subjectIds)->where('demo', 0);
    }

    private function getAvailableSubjectsForSchoolLocation(SchoolLocation $schoolLocation)
    {
        return $this->whereIn('id', Teacher::whereIn('class_id', $schoolLocation->schoolClasses()->pluck('id')
        )->pluck('subject_id')->unique())->get();
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

        $schoolLocations = SchoolLocation::whereIn('customer_code', Arr::wrap($customerCodes))->get();

        $baseSubjectIds = $user->subjects()->pluck('base_subject_id')->unique();

        $subjectIds = collect([]);

        foreach($schoolLocations as $school_location)
        {
            $subjects = collect([]);
            foreach ($school_location->schoolLocationSections as $schoolLocationSection) {
                $subjects = $subjects->merge($schoolLocationSection->subjects);
            }
            $subjectIds = $subjectIds->merge($subjects->whereIn('base_subject_id', $baseSubjectIds)->pluck('id')->unique()->toArray());
        }
        return $subjectIds->toArray();
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
        if($user->isValidExamCoordinator()) {
            //This returns a queryBuilder for efficiency purposes.
            return Subject::select('id')->whereIn('base_subject_id', BaseSubject::select('id')->distinct());
        }

        return Subject::getSubjectIdsOfSchoolLocationByCustomerCodesAndUser(Arr::wrap($customer_codes), $user);
    }
}

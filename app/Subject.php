<?php namespace tcCore;

use Closure;
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
                    $schoolYear = SchoolYearRepository::getCurrentSchoolYear();
                    $query->whereIn('id', function ($query) use ($value, $schoolYear) {
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
                    });
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

    public function scopeCitoFiltered($query, $filters = [], $sorting = [])
    {
        $citoSchool = SchoolLocation::where('customer_code', 'CITO-TOETSENOPMAAT')->first();

        return $this->filterByUserAndSchoolLocation($query, $citoSchool);
    }

    public function scopeExamFiltered($query, $filters = [], $sorting = [])
    {
        $examSchool = SchoolLocation::where('customer_code', config('custom.examschool_customercode'))->first();

        return $this->filterByUserAndSchoolLocation($query, $examSchool);
    }

    public function scopeNationalItemBankFiltered($query, $filters = [], $sorting = [])
    {
        $nationalItemBankSchool = SchoolLocation::where('customer_code', config('custom.national_item_bank_school_customercode'))->first();

        return $this->filterByUserAndSchoolLocation($query, $nationalItemBankSchool);
    }

    private function filterByUserAndSchoolLocation($query, $schoolLocation)
    {
        if (!$schoolLocation) { // slower but as a fallback in case there's no cito school
            $query->where('subjects.id', -1);
            return $query;
        }

        $user = Auth::user();

        $subjectIds = $this->getAvailableSubjectsForSchoolLocation($schoolLocation)
            ->whereIn('base_subject_id', $this->getBaseSubjectsForUser($user))
            ->pluck('id')
            ->unique()
            ->toArray();

        $query->whereIn('id', $subjectIds);
        return $query;
    }

    private function getAvailableSubjectsForSchoolLocation(SchoolLocation $schoolLocation)
    {
        return $this->whereIn('id', Teacher::whereIn('class_id', $schoolLocation->schoolClasses()->pluck('id')
        )->pluck('subject_id')->unique())->get();
    }

    private function getBaseSubjectsForUser(User $user)
    {
        return $user->subjects()->pluck('base_subject_id')->unique();
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

    public static function getSubjectsOfCustomSchoolForUser($customerCode, $user): array
    {
        $school = SchoolLocation::where('customer_code', $customerCode)->first();
        $baseSubjectIds = $user->subjects()->pluck('base_subject_id')->unique();

        if ($school) {
            $subjects = collect([]);
            foreach ($school->schoolLocationSections as $schoolLocationSection) {
                $subjects = $subjects->merge($schoolLocationSection->subjects);
            }
            return $subjects->whereIn('base_subject_id', $baseSubjectIds)->pluck('id')->unique()->toArray();
        }
        return [];
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


}

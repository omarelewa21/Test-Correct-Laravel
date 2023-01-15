<?php namespace tcCore;

use Carbon\Carbon;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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

class Period extends BaseModel implements AccessCheckable {

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
    protected $table = 'periods';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['school_year_id', 'name', 'start_date', 'end_date'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function schoolYear() {
        return $this->belongsTo('tcCore\SchoolYear');
    }

    public function tests() {
        return $this->hasMany('tcCore\Tests');
    }

    public function testTakes() {
        return $this->hasMany('tcCore\TestTakes');
    }

    public function pValues() {
        return $this->hasMany('tcCore\PValue');
    }

    public function ratings() {
        return $this->hasMany('tcCore\Rating');
    }

    public function isActual(){
        if ($this->start_date <= Carbon::today() && $this->end_date >= Carbon::today()){
            return true;
        }
        return false;
    }

    public static function boot()
    {
        parent::boot();

        // addd for the onboarding experience 20200506
        // REMARK: based on current logged in user!!!!
        static::created(function (Period $period) {
            if ($period->start_date <= Carbon::today() && $period->end_date >= Carbon::today()) { // current period
                $helper = new DemoHelper();
                $schoolYear = $period->schoolYear;
                $user = Auth::user();

                if(null === optional($user)->schoolLocation){
                    $period->forceDelete();
                    throw new \Exception('U kunt een periode alleen aanmaken als een gebruiker van een schoollocatie. Dit doet u door als schoolbeheerder in het menu Database -> Schooljaren een schooljaar aan te maken met een periode die in de huidige periode valt.');
                }
                $helper->createDemoClassForSchoolLocationAndPopulate($user->schoolLocation, $schoolYear);
            }
        });
    }

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        $roles = Roles::getUserRoles();
        if (!in_array('Administrator', $roles)) {
            $query->whereIn('school_year_id', function ($query) {
                $query->select('id')->from(with(new SchoolYear())->getTable())->whereNull('deleted_at');
                with(new SchoolYear())->scopeFiltered($query);
            });
        }

        foreach($filters as $key => $value) {
            switch($key) {
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
                case 'school_year_id':
                    if (is_array($value)) {
                        $query->whereIn('school_year_id', $value);
                    } else {
                        $query->where('school_year_id', '=', $value);
                    }
                    break;
                default:
                    break;
            }
        }

        foreach($sorting as $key => $value) {
            switch(strtolower($value)) {
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

            switch(strtolower($key)) {
                case 'id':
                case 'name':
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

        return SchoolYear::filtered(['id' => $this->getAttribute('school_year_id')])->count() > 0;
    }

    public function canAccessBoundResource($request, Closure $next) {
        return $this->canAccess();
    }

    public function getAccessDeniedResponse($request, Closure $next)
    {
        throw new AccessDeniedHttpException('Access to period denied');
    }

    public function scopeCurrentlyActive($query)
    {
        return $query->where('start_date', '<=', Carbon::today())
            ->where('end_date', '>=', Carbon::today());
    }

    public function scopeForSchoolLocation($query, SchoolLocation $schoolLocation)
    {
        return $query->whereIn(
            'school_year_id',
            SchoolLocationSchoolYear::select('school_year_id')
                ->where('school_location_id', $schoolLocation->getKey())
        );
    }
}

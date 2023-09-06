<?php namespace tcCore;

use Closure;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use tcCore\Lib\Models\AccessCheckable;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Lib\User\Roles;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use tcCore\Traits\UuidTrait;

class SchoolYear extends BaseModel implements AccessCheckable {

    use SoftDeletes;
    use UuidTrait;

    protected $casts = [
        'uuid'       => EfficientUuid::class,
        'deleted_at' => 'datetime',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'school_years';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['year'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $schoolLocations;

    public function fill(array $attributes)
    {
        parent::fill($attributes);

        if (array_key_exists('school_locations', $attributes)) {
            $this->schoolLocations = $attributes['school_locations'];
        } elseif(array_key_exists('add_school_location', $attributes) || array_key_exists('delete_school_location', $attributes)) {
            $this->schoolLocations = $this->schoolLocationSchoolYears()->pluck('school_location_id')->all();
            if (array_key_exists('add_school_location', $attributes)) {
                array_push($this->schoolLocations, $attributes['add_school_location']);
            }

            if (array_key_exists('delete_school_location', $attributes)) {
                if(($key = array_search($attributes['delete_school_location'], $this->schoolLocations)) !== false) {
                    unset($this->schoolLocations[$key]);
                }
            }
        }
    }

    public static function boot()
    {
        parent::boot();

        // Progress additional answers
        static::saved(function(SchoolYear $schoolYear)
        {
            if ($schoolYear->schoolLocations !== null) {
                $schoolYear->saveSchoolLocations();
            }
        });
    }

    public function schoolClasses() {
        return $this->hasMany('tcCore\SchoolClass');
    }

    public function teachers() {
        return $this->hasMany('tcCore\Teacher');
    }

    public function periods() {
        return $this->hasMany('tcCore\Period');
    }

    protected function saveSchoolLocations() {
        $schoolLocationSchoolYears = $this->schoolLocationSchoolYears()->withTrashed()->get();
        $this->syncTcRelation($schoolLocationSchoolYears, $this->schoolLocations, 'school_location_id', function($schoolYear, $schoolLocationId) {
            SchoolLocationSchoolYear::create(['school_year_id' => $schoolYear->getKey(), 'school_location_id' => $schoolLocationId]);
        });

        $this->schoolLocations = null;
    }

    public function schoolLocationSchoolYears() {
        return $this->hasMany('tcCore\SchoolLocationSchoolYear');
    }

    public function schoolLocations() {
        return $this->belongsToMany('tcCore\SchoolLocation', 'school_location_school_years', 'school_year_id', 'school_location_id')->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()])->wherePivot($this->getDeletedAtColumn(), null);
    }

    public function averageRatings() {
        return $this->hasMany('tcCore\AverageRating');
    }

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        $roles = Roles::getUserRoles();
        if (!in_array('Administrator', $roles)) {
            $query->whereIn('id', function ($query) {
                $query->select('school_year_id')
                    ->from(with(new SchoolLocationSchoolYear())->getTable())
                    ->whereIn('school_location_id', function ($query) {
                        $query->select('id')->from(with(new SchoolLocation())->getTable());
                        with(new SchoolLocation())->scopeFiltered($query)->select('id');
                    })->whereNull('deleted_at');
            });
        }

        foreach($filters as $key => $value) {
            switch($key) {
                default:
                    break;
            }
        }

        foreach($sorting as $key => $value) {
            switch(strtolower($value)) {
                case 'id':
                case 'year':
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
                case 'year':
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

        return SchoolLocationSchoolYear::where('school_year_id', $this->getKey())->whereIn('school_location_id', function ($query) {
            $query->select('id')->from(with(new SchoolLocation())->getTable());
            with(new SchoolLocation())->scopeFiltered($query);
        })->count() > 0;
    }

    public function canAccessBoundResource($request, Closure $next) {
        return $this->canAccess();
    }

    public function getAccessDeniedResponse($request, Closure $next)
    {
        throw new AccessDeniedHttpException('Access to school year denied');
    }


}

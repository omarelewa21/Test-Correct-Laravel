<?php namespace tcCore;

use Closure;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use tcCore\Lib\Models\AccessCheckable;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Lib\User\Roles;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use tcCore\Traits\UuidTrait;

class Section extends BaseModel implements AccessCheckable {

    use SoftDeletes;
    use UuidTrait;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'sections';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name','demo'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    protected $casts = [
        'demo'       => 'boolean',
        'uuid'       => EfficientUuid::class,
        'deleted_at' => 'datetime',
    ];

    protected $schoolLocations;

    public function fill(array $attributes)
    {
        parent::fill($attributes);

        if (array_key_exists('school_locations', $attributes)) {
            $this->schoolLocations = $attributes['school_locations'];
        } elseif(array_key_exists('add_school_location', $attributes) || array_key_exists('delete_school_location', $attributes)) {
            $this->schoolLocations = $this->schoolLocationSections()->pluck('school_location_id')->all();
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

        static::updating(function (self $item) {
            if ($item->getOriginal('demo') == true) return false;
        });

        static::deleting(function (self $item) {
            if ($item->getOriginal('demo') == true) return false;
        });

        // Progress additional answers
        static::saved(function(Section $section)
        {
            if ($section->schoolLocations !== null) {
                $section->saveSchoolLocations();
            }
        });
    }

    public function subjects() {
        return $this->hasMany('tcCore\Subject');
    }

    protected function saveSchoolLocations() {
        $schoolLocationSections = $this->schoolLocationSections()->withTrashed()->get();
        $this->syncTcRelation($schoolLocationSections, $this->schoolLocations, 'school_location_id', function($section, $schoolLocationId) {
            SchoolLocationSection::create(['section_id' => $section->getKey(), 'school_location_id' => $schoolLocationId]);
        });

        $this->schoolLocations = null;
    }

    public function schoolLocationSections() {
        return $this->hasMany('tcCore\SchoolLocationSection');
    }

    public function schoolLocations() {
        return $this->belongsToMany('tcCore\SchoolLocation', 'school_location_sections', 'section_id', 'school_location_id')->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()])->wherePivot($this->getDeletedAtColumn(), null);
    }

    public function sharedSchoolLocations()
    {
        return $this->belongsToMany(SchoolLocation::class,'school_location_shared_sections')->withTimestamps();
    }

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        $roles = Roles::getUserRoles();
        if (!in_array('Administrator', $roles)) {
            $query->whereIn('id', function ($query) {
                $query->select('section_id')
                    ->from(with(new SchoolLocationSection())->getTable())
                    ->whereIn('school_location_id', function ($query) {
                        $query->select('id')->from(with(new SchoolLocation())->getTable());
                        with(new SchoolLocation())->scopeFiltered($query);
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

        return Section::filtered(['id' => $this->getAttribute('school_year_id')])->count() > 0;
    }

    public function canAccessBoundResource($request, Closure $next) {
        return $this->canAccess();
    }

    public function getAccessDeniedResponse($request, Closure $next)
    {
        throw new AccessDeniedHttpException('Access to section denied');
    }


}

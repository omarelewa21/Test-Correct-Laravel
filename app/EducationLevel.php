<?php namespace tcCore;

use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;

class EducationLevel extends BaseModel {

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

    public function schoolClasses() {
        return $this->hasMany('tcCore\SchoolClass');
    }

    public function tests() {
        return $this->hasMany('tcCore\Test');
    }

    public function attainments() {
        return $this->hasMany('tcCore\Attainment');
    }

    public function pValue() {
        return $this->hasMany('tcCore\PValue');
    }

    public function ratings() {
        return $this->hasMany('tcCore\Rating');
    }

    public static function boot()
    {
        parent::boot();

        // Progress additional answers
        static::saved(function(EducationLevel $educationLevel)
        {
            if ($educationLevel->schoolLocations !== null) {
                $educationLevel->saveSchoolLocations();
            }
        });
    }

    protected function saveSchoolLocations() {
        $schoolLocationEducationLevels = $this->schoolLocationEducationLevels()->withTrashed()->get();
        $this->syncTcRelation($schoolLocationEducationLevels, $this->schoolLocations, 'school_location_id', function($educationLevel, $schoolLocationId) {
            SchoolLocationEducationLevel::create(['education_level_id' => $educationLevel->getKey(), 'school_location_id' => $schoolLocationId]);
        });

        $this->schoolLocations = null;
    }

    public function schoolLocationEducationLevels() {
        return $this->hasMany('tcCore\SchoolLocationEducationLevel');
    }

    public function schoolLocations() {
        return $this->belongsToMany('tcCore\SchoolLocation', 'school_location_education_levels', 'education_level_id', 'school_location_id')->withPivot([$this->getCreatedAtColumn(), $this->getUpdatedAtColumn(), $this->getDeletedAtColumn()])->wherePivot($this->getDeletedAtColumn(), null);
    }

    public function scopeFiltered($query, $filters = [], $sorting = [])
    {
        foreach($filters as $key => $value) {
            switch($key) {
                case 'user_id':
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
                    break;
            }
        }

        //Todo: More sorting
        foreach($sorting as $key => $value) {
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
}

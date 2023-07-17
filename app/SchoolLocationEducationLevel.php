<?php namespace tcCore;

use tcCore\Lib\Models\CompositePrimaryKeyModel;
use tcCore\Lib\Models\CompositePrimaryKeyModelSoftDeletes;

class SchoolLocationEducationLevel extends CompositePrimaryKeyModel {

    use CompositePrimaryKeyModelSoftDeletes;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $casts = ['deleted_at' => 'datetime',];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'school_location_education_levels';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['school_location_id', 'education_level_id'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = ['school_location_id', 'education_level_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function schoolLocation() {
        return $this->belongsTo('tcCore\SchoolLocation');
    }

    public function educationLevel() {
        return $this->belongsTo('tcCore\EducationLevel');
    }
}

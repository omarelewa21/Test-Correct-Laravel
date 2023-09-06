<?php namespace tcCore;

use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Lib\Models\CompositePrimaryKeyModel;
use tcCore\Lib\Models\CompositePrimaryKeyModelSoftDeletes;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use tcCore\Traits\UuidTrait;

class SchoolLocationSchoolYear extends CompositePrimaryKeyModel {

    use CompositePrimaryKeyModelSoftDeletes;
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
    protected $table = 'school_location_school_years';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['school_location_id', 'school_year_id'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = ['school_location_id', 'school_year_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function schoolLocation() {
        return $this->belongsTo('tcCore\SchoolLocation');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     * why is this here with such a name? 20200507 Erik
     */
    public function period() {
        return $this->belongsTo('tcCore\SchoolYear');
    }

    public function schoolYear() {
        return $this->belongsTo('tcCore\SchoolYear');
    }


}

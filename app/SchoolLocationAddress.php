<?php namespace tcCore;

use tcCore\Lib\Models\CompositePrimaryKeyModel;
use tcCore\Lib\Models\CompositePrimaryKeyModelSoftDeletes;

class SchoolLocationAddress extends CompositePrimaryKeyModel {

    use CompositePrimaryKeyModelSoftDeletes;

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
    protected $table = 'school_location_addresses';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['address_id', 'school_location_id', 'type'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = ['address_id', 'school_location_id', 'type'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function address() {
        return $this->belongsTo('tcCore\Address');
    }

    public function schoolLocation() {
        return $this->belongsTo('tcCore\SchoolLocation');
    }
}

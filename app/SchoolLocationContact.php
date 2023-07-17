<?php namespace tcCore;

use tcCore\Lib\Models\CompositePrimaryKeyModel;
use tcCore\Lib\Models\CompositePrimaryKeyModelSoftDeletes;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use tcCore\Traits\UuidTrait;

class SchoolLocationContact extends CompositePrimaryKeyModel {

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
    protected $table = 'school_location_contacts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['contact_id', 'school_location_id', 'type'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = ['contact_id', 'school_location_id', 'type'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function contact() {
        return $this->belongsTo('tcCore\Contact');
    }

    public function schoolLocation() {
        return $this->belongsTo('tcCore\SchoolLocation');
    }


}

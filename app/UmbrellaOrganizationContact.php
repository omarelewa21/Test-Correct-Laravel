<?php namespace tcCore;

use tcCore\Lib\Models\CompositePrimaryKeyModel;
use tcCore\Lib\Models\CompositePrimaryKeyModelSoftDeletes;

class UmbrellaOrganizationContact extends CompositePrimaryKeyModel {

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
    protected $table = 'umbrella_organization_contacts';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['contact_id', 'umbrella_organization_id', 'type'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = ['contact_id', 'umbrella_organization_id', 'type'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function contact() {
        return $this->belongsTo('tcCore\Contact');
    }

    public function umbrellaOrganization() {
        return $this->belongsTo('tcCore\UmbrellaOrganization');
    }
}

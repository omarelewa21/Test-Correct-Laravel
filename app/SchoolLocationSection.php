<?php namespace tcCore;

use tcCore\Lib\Models\CompositePrimaryKeyModel;
use tcCore\Lib\Models\CompositePrimaryKeyModelSoftDeletes;

class SchoolLocationSection extends CompositePrimaryKeyModel {

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
    protected $table = 'school_location_sections';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['school_location_id', 'section_id','demo'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = ['school_location_id', 'section_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function schoolLocation() {
        return $this->belongsTo('tcCore\SchoolLocation');
    }

    public function subject() { // who thinks of such a name and call it subject instead of section 20200508 Erik???
        return $this->belongsTo('tcCore\Section');
    }

    public function section() {
        return $this->belongsTo('tcCore\Section');
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
    }
}

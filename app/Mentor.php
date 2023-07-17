<?php namespace tcCore;

use tcCore\Lib\Models\CompositePrimaryKeyModel;
use tcCore\Lib\Models\CompositePrimaryKeyModelSoftDeletes;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use tcCore\Traits\UuidTrait;

class Mentor extends CompositePrimaryKeyModel {

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
    protected $table = 'mentors';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['school_class_id', 'user_id'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = ['school_class_id', 'user_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function schoolClass() {
        return $this->belongsTo('tcCore\SchoolClass');
    }

    public function user() {
        return $this->belongsTo('tcCore\User');
    }


}

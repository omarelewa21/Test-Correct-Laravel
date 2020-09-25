<?php namespace tcCore;

use tcCore\Lib\Models\CompositePrimaryKeyModel;
use tcCore\Lib\Models\CompositePrimaryKeyModelSoftDeletes;
use Dyrynda\Database\Casts\EfficientUuid;
use Dyrynda\Database\Support\GeneratesUuid;
use tcCore\Traits\UuidTrait;

class Invigilator extends CompositePrimaryKeyModel {

    use CompositePrimaryKeyModelSoftDeletes;
    use UuidTrait;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

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
    protected $table = 'invigilators';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['test_take_id', 'user_id'];

    /**
     * The primary key for the model.
     *
     * @var string
     */
    protected $primaryKey = ['test_take_id', 'user_id'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function testTake() {
        return $this->belongsTo('tcCore\TestTake');
    }

    public function user() {
        return $this->belongsTo('tcCore\User');
    }

    public function getRouteKeyName()
    {
        return 'uuid';
    }
}

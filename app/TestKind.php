<?php namespace tcCore;

use Dyrynda\Database\Casts\EfficientUuid;
use tcCore\Lib\Models\BaseModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Traits\UuidTrait;

class TestKind extends BaseModel
{

    use SoftDeletes, UuidTrait;

    const ASSIGNMENT_TYPE = 4;

    protected $casts = [
        'uuid'       => EfficientUuid::class,
        'deleted_at' => 'datetime',
    ];

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'test_kinds';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['name', 'has_weight'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function tests()
    {
        return $this->hasMany('tcCore\Test');
    }
}

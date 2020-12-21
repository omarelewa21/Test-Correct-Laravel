<?php

namespace tcCore;

use Dyrynda\Database\Casts\EfficientUuid;
use tcCore\Lib\Models\BaseModel;
use tcCore\Traits\UuidTrait;

class ShortcodeClick extends BaseModel
{

    use UuidTrait;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at','created_at','updated_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['ip','user_id','shortcode_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }


}

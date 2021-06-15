<?php

namespace tcCore;

use Dyrynda\Database\Casts\EfficientUuid;
use Illuminate\Database\Eloquent\Model;
use tcCore\Traits\UuidTrait;

class SamlMessage extends Model
{
    use UuidTrait;

    protected $casts = [
        'uuid'    => EfficientUuid::class,
    ];

    protected $fillable = [
        'message_id',
        'email',
        'eckid'
    ];
}

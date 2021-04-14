<?php

namespace tcCore;

use Dyrynda\Database\Casts\EfficientUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Traits\UuidTrait;

class MaintenanceWhitelistIp extends Model
{
    use SoftDeletes;
    use UuidTrait;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

    protected $fillable = [
      'ip',
      'name'
    ];
}

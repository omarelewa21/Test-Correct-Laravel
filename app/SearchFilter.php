<?php

namespace tcCore;

use Illuminate\Database\Eloquent\Model;
use Dyrynda\Database\Casts\EfficientUuid;
use tcCore\Traits\UuidTrait;

class SearchFilter extends Model
{
    protected $guarded = [];

    use UuidTrait;

    protected $casts = [
        'uuid' => EfficientUuid::class,
        'filters' => 'array',
    ];
}

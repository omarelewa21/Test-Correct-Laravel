<?php

namespace tcCore;

use Carbon\Carbon;
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
        'eck_id',
        'data',
    ];

    public static function getSamlMessageIfValid($uuid)
    {
        $message = SamlMessage::whereUuid($uuid)->first();
        if ($message == null) {
            return null;
        }

        if ($message->created_at < Carbon::now()->subMinutes(1)->toDateTimeString()) {
            return null;
        }

        return true;
    }
}

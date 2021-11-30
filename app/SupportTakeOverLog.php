<?php

namespace tcCore;

use Dyrynda\Database\Casts\EfficientUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use tcCore\Traits\UuidTrait;

class SupportTakeOverLog extends Model
{
    use SoftDeletes, UuidTrait;


    /**
     * @var string[]
     */
    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['support_user_id','user_id', 'ip'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function supportUser()
    {
        return $this->belongsTo(User::class, 'support_user_id');
    }

    public static function createForUserWithSupportUserAndIp(User $user, User $supportUser, $ip)
    {
        return self::create([
            'user_id' => $user->getKey(),
            'support_user_id' => $supportUser->getKey(),
            'ip' => $ip
        ]);
    }
}

<?php

namespace tcCore;

use Carbon\Carbon;
use Dyrynda\Database\Casts\EfficientUuid;
use Illuminate\Database\Eloquent\Model;
use tcCore\Traits\UuidTrait;

class TemporaryLogin extends Model
{
    use UuidTrait;

    const MAX_VALID_IN_SECONDS = 5;

    protected $casts = [
        'uuid' => EfficientUuid::class,
    ];


    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'temporary_login';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['user_id', 'uuid'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function createCakeUrl() {
        return config('app.url_login').'users/temporary_login/'.$this->uuid;
    }

    public static function createForUser(User $user)
    {
        self::where('user_id', $user->getKey())->forceDelete();

        return self::create(['user_id' => $user->getKey()]);
    }

    public static function isValid($uuid)
    {
        $result = false;
        $temporary_login = self::whereUuid($uuid)->first();

        if ($temporary_login && Carbon::now()->diffInSeconds($temporary_login->created_at) < self::MAX_VALID_IN_SECONDS) {
            $result = $temporary_login->user_id;
            // only valid once;
            $temporary_login->forceDelete();
        }

        return $result;
    }
}

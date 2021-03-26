<?php

namespace tcCore;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class FailedLogin extends Model
{
    protected const COUNT_SINCE_IN_MINUTES = 30;
    protected const EXTRA_MEASURES_AFTER_FAILURE_COUNT = 3;

    protected $fillable = [
        'username',
        'ip',
        'solved',
    ];

    protected $casts = [
      'solved' => 'boolean',
      'ip' => 'string',
      'username' => 'string',
    ];

    public static function doWeNeedExtraSecurityLayer($username) : bool
    {
        return !!(static::where('solved',false)
            ->where('created_at','>=',Carbon::now()->subMinutes(static::COUNT_SINCE_IN_MINUTES))
            ->where('username',$username)
            ->count() >= static::EXTRA_MEASURES_AFTER_FAILURE_COUNT);
    }

    public static function solveForUsernameAndIp($username, $ip) : void
    {
        logger('solve for '.$username.' and ip '.$ip);
        \DB::table('failed_logins')
            ->where('solved',false)
            ->where('username',$username)
            ->where('ip',$ip)
            ->update(['solved' => true]);
    }
}

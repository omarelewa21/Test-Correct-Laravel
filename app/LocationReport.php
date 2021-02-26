<?php

namespace tcCore;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class LocationReport extends Model
{
    protected $guarded = [];

    public static function updateAll(User $user)
    {

        // what is the location id?
        
        self::updateOrCreate([
            'location_id' => $location_id,
        ], ['invited_users'                               => self::invitedUsers($user),
            'account_verified'                            => $user->account_verified
        ]);
        
    }

    

}

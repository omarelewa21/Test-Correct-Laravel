<?php

namespace tcCore\Events;

use Illuminate\Support\Facades\Auth;

class NewTestTakePlanned extends UserPrivateEvent
{
    public function broadcastAs()
    {
        return 'NewTestTakePlanned';
    }

    public static function channel()
    {
        return 'echo-private:User.' . Auth::user()->uuid . ',.NewTestTakePlanned';
    }
}

<?php

namespace tcCore\Events;

use Illuminate\Support\Facades\Auth;

class NewTestTakePlanned extends UserPrivateEvent
{
    public function broadcastAs()
    {
        return 'NewTestTakePlanned';
    }
}

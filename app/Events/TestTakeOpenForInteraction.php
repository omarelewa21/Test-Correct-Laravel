<?php

namespace tcCore\Events;

use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;

class TestTakeOpenForInteraction extends TestParticipantEvent
{
    public function broadcastAs()
    {
        return 'TestTakeOpenForInteraction';
    }
}
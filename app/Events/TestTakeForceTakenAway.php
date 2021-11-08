<?php

namespace tcCore\Events;

class TestTakeForceTakenAway extends TestParticipantEvent
{
    public function broadcastAs()
    {
        return 'TestTakeForceTakenAway';
    }
}

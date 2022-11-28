<?php

namespace tcCore\Events;

class TestTakeReopened extends TestParticipantEvent
{
    public function broadcastAs()
    {
        return 'TestTakeReopened';
    }
}

<?php

namespace tcCore\Events;

class RemoveParticipantFromWaitingRoom extends TestParticipantEvent
{
    public function broadcastAs()
    {
        return 'RemoveParticipantFromWaitingRoom';
    }
}

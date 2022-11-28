<?php

namespace tcCore\Events;

class TestParticipantGuestAvailabilityChanged extends TestTakePublicEvent
{
    public function broadcastAs()
    {
        return 'TestParticipantGuestAvailabilityChanged';
    }
}

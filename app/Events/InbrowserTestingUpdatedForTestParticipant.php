<?php

namespace tcCore\Events;

class InbrowserTestingUpdatedForTestParticipant extends TestParticipantEvent
{
    public function broadcastAs()
    {
        return 'InbrowserTestingUpdatedForTestParticipant';
    }
}

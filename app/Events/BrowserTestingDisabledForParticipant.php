<?php

namespace tcCore\Events;

class BrowserTestingDisabledForParticipant extends TestParticipantEvent
{
    public function broadcastAs()
    {
        return 'BrowserTestingDisabledForParticipant';
    }
}

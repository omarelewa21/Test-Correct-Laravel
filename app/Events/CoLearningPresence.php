<?php

namespace tcCore\Events;

class CoLearningPresence extends TestTakePresenceEvent
{
    public function broadcastAs()
    {
        return 'CoLearningPresence';
    }
}
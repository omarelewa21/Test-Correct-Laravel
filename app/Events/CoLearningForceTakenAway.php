<?php

namespace tcCore\Events;

class CoLearningForceTakenAway extends TestParticipantEvent
{
    public function broadcastAs()
    {
        return 'CoLearningForceTakenAway';
    }
}

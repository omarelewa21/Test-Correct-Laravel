<?php

namespace tcCore\Events;

class CoLearningNextQuestion extends TestParticipantEvent
{
    public function broadcastAs()
    {
        return 'CoLearningNextQuestion';
    }
}

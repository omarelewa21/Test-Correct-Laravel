<?php
namespace tcCore\Events;

class RemoveFraudDetectionNotification extends TestParticipantEvent
{
    public function broadcastAs()
    {
        return 'RemoveFraudDetectionNotification';
    }
}
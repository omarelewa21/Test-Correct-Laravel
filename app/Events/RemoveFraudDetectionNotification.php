<?php
namespace tcCore\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use tcCore\TestParticipant;

class RemoveFraudDetectionNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $testParticipant;

    public function __construct(TestParticipant $testParticipant)
    {
        $this->testParticipant = $testParticipant;
        logger('initiating RemoveFraudDetectionNotification');
    }

    public function broadcastOn()
    {
        return new PrivateChannel('TestParticipant.'.$this->testParticipant->getKey());
    }

    public function broadcastAs()
    {
        return 'RemoveFraudDetectionNotification';
    }
}
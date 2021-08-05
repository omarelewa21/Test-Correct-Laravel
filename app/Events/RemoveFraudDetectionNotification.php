<?php
namespace tcCore\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Queue\SerializesModels;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use tcCore\TestParticipant;
use tcCore\TestTake;

class RemoveFraudDetectionNotification implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $testTake, $userId;

    public function __construct(TestTake $testTake, $testParticipantId)
    {
        $this->testTake = $testTake;
        $this->userId = TestParticipant::whereId($testParticipantId)->value('user_id');
    }

    public function broadcastOn()
    {
        return new PrivateChannel('TestTake.'.$this->testTake->uuid);
    }

//    public function broadcastWith()
//    {
//        return ['user_id' => $this->userId];
//    }

    public function broadcastAs()
    {
        return 'RemoveFraudDetectionNotification';
    }
}
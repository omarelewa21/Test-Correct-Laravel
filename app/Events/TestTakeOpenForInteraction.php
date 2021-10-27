<?php

namespace tcCore\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use tcCore\TestParticipant;

class TestTakeOpenForInteraction implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $testParticipant;
    public $status;

    public function __construct($testParticipantId, $status)
    {
        $this->testParticipantId = $testParticipantId;
        $this->status = $status;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('TestParticipant.'.$this->testParticipantId);
    }

    public function broadcastAs()
    {
        return 'TestTakeOpenForInteraction';
    }
}

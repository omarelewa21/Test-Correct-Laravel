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

    public function __construct(TestParticipant $testParticipant, $status)
    {
        $this->testParticipant = $testParticipant;
        $this->status = $status;
    }

    public function broadcastOn()
    {
        return new PrivateChannel('TestParticipant.'.$this->testParticipant->getKey());
    }

    public function broadcastAs()
    {
        return 'TestTakeOpenForInteraction';
    }
}

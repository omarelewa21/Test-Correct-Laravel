<?php

namespace tcCore\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class TestTakePublicEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    private $testTakeUuid;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($testTakeUuid)
    {
        $this->testTakeUuid = $testTakeUuid;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return Channel
     */
    public function broadcastOn()
    {
        return new Channel('TestTake.'.$this->testTakeUuid);
    }
}
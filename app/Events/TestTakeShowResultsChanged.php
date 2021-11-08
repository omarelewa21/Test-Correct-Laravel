<?php

namespace tcCore\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use tcCore\TestTake;

class TestTakeShowResultsChanged implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var TestTake
     */
    private $testTake;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(TestTake $testTake)
    {
        $this->testTake = $testTake;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PresenceChannel('Presence-TestTake.'.$this->testTake->uuid);
    }

    public function broadcastAs()
    {
        return 'TestTakeShowResultsChanged';
    }
}

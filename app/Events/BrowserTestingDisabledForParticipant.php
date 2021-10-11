<?php

namespace tcCore\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use tcCore\TestParticipant;

class BrowserTestingDisabledForParticipant implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $testParticipant;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(TestParticipant $testParticipant)
    {
        $this->testParticipant = $testParticipant;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('TestParticipant.'.$this->testParticipant->getKey());
    }

    public function broadcastAs()
    {
        return 'BrowserTestingDisabledForParticipant';
    }
}

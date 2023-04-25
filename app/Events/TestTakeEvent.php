<?php

namespace tcCore\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

abstract class TestTakeEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $testTakeUuid;

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
     * @return PrivateChannel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('TestTake.'.$this->testTakeUuid);
    }

    public static function channelSignature(string $testTakeUuid)
    {
        $eventName = class_basename(get_called_class());

        return "echo-private:TestTake.$testTakeUuid,.$eventName";
    }

    public function broadcastAs()
    {
        return class_basename(get_called_class());
    }
}
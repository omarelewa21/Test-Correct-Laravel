<?php

namespace tcCore\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

abstract class TestTakePresenceEvent implements ShouldBroadcastNow
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
     * @return PresenceChannel
     */
    public function broadcastOn()
    {
        return new PresenceChannel('presence-TestTake.'.$this->testTakeUuid);
    }

    public static function channelSignature($testTakeUuid)
    {
        $eventName = class_basename(get_called_class());
        return "echo-presence:presence-TestTake.$testTakeUuid,.$eventName";
    }

    public static function channelHereSignature($testTakeUuid)
    {
        return "echo-presence:presence-TestTake.$testTakeUuid,.here";
    }

    public static function channelJoiningSignature($testTakeUuid)
    {
        return "echo-presence:presence-TestTake.$testTakeUuid,.joining";
    }

    public static function channelLeavingSignature($testTakeUuid)
    {
        return "echo-presence:presence-TestTake.$testTakeUuid,.leaving";
    }
}
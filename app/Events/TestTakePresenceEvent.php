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
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    protected static string $channelSuffix = '';

    protected string $testTakeUuid;

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
    public function broadcastOn(): PresenceChannel
    {
        return new PresenceChannel('presence-TestTake.' . $this->testTakeUuid);
    }

    private static function channelBase($uuid): string
    {
        return sprintf("echo-presence:presence-TestTake%s.%s,", self::$channelSuffix, $uuid);
    }

    public static function channelSignature($testTakeUuid): string
    {
        $eventName = class_basename(get_called_class());
        return self::channelBase($testTakeUuid) . ".$eventName";
    }

    public static function channelHereSignature($testTakeUuid): string
    {
        return self::channelBase($testTakeUuid) . "here";
    }

    public static function channelJoiningSignature($testTakeUuid): string
    {
        return self::channelBase($testTakeUuid) . "joining";
    }

    public static function channelLeavingSignature($testTakeUuid): string
    {
        return self::channelBase($testTakeUuid) . "leaving";
    }
}
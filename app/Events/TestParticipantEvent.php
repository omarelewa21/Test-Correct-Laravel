<?php

namespace tcCore\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

abstract class TestParticipantEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $testParticipantUuid;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($testParticipantUuid)
    {
        $this->testParticipantUuid = $testParticipantUuid;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return PrivateChannel
     */
    public function broadcastOn()
    {
        return new PrivateChannel('TestParticipant.'.$this->testParticipantUuid);
    }

    public static function channelSignature($testParticipantUuid)
    {
        $eventName = class_basename(get_called_class());

        return "echo-private:TestParticipant.$testParticipantUuid,.$eventName";
    }
}
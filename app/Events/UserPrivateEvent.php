<?php

namespace tcCore\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

abstract class UserPrivateEvent implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    protected $userUuid;

    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct($userUuid)
    {
        $this->userUuid = $userUuid;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('User.'.$this->userUuid);
    }

    public static function channelSignature()
    {
        $userUuid = Auth::user()->uuid;
        $eventName = class_basename(get_called_class());

        return "echo-private:User.$userUuid,.$eventName";
    }
}
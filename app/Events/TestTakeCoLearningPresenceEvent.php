<?php

namespace tcCore\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;

abstract class TestTakeCoLearningPresenceEvent extends TestTakePresenceEvent
{
    protected static string $channelSuffix = '-CoLearning';
}
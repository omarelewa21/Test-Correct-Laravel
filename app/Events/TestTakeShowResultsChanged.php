<?php

namespace tcCore\Events;

class TestTakeShowResultsChanged extends TestTakePresenceEvent
{
    public function broadcastAs()
    {
        return 'TestTakeShowResultsChanged';
    }
}

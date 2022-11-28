<?php

namespace tcCore\Events;

class NewTestTakeEventAdded extends TestTakePublicEvent
{
    public function broadcastAs()
    {
        return 'NewTestTakeEventAdded';
    }
}

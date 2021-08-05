<?php

namespace tcCore\Broadcasting;

use tcCore\TestTake;
use tcCore\User;

class TestTakeChannel
{
    /**
     * Create a new channel instance.
     *
     * @return void
     */
    public function __construct()
    {
        //;
    }

    /**
     * Authenticate the user's access to the channel.
     *
     * @param User $user
     * @param $testTakeUuid
     * @return array|bool
     */
    public function join(User $user, $testTakeUuid)
    {
        $testTake = TestTake::whereUuid($testTakeUuid)->with('testParticipants')->firstOrFail();
        return $testTake->testParticipants->each(function ($tp) use ($user) {
            if ($tp->user_id === $user->getKey()) {
                return true;
            }
        });
    }
}

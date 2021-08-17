<?php

use Illuminate\Support\Facades\Broadcast;

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/


Broadcast::channel('TestTake.{testTakeUuid}', \tcCore\Broadcasting\TestTakeChannel::class);
Broadcast::channel('TestParticipant.{testParticipantId}', function($user, $testParticipantId) {
    $testParticipantUserId = \tcCore\TestParticipant::whereId($testParticipantId)->value('user_id');
    return $testParticipantUserId === $user->getKey();
});

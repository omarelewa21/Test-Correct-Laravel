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


Broadcast::channel('Presence-TestTake.{testTakeUuid}', function($user) {
    return [
        'id' => $user->getKey(),
        'uuid' => $user->uuid,
        'name' => $user->getNameFullAttribute(),
        'guest' => $user->guest
    ];
});
Broadcast::channel('TestTake.{testTakeUuid}', function() {
    return true;
});
Broadcast::channel('TestParticipant.{testParticipantId}', function($user, $testParticipantId) {
    $testParticipantUserId = \tcCore\TestParticipant::whereId($testParticipantId)->value('user_id');
    return $testParticipantUserId === $user->getKey();
});

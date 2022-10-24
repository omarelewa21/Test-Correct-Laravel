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


Broadcast::channel('presence-TestTake.{testTakeUuid}', function ($user) {
    return [
        'uuid'    => $user->uuid,
        'name'    => $user->getNameFullAttribute(),
        'guest'   => $user->guest,
        'student' => $user->isA('Student')
    ];
});
Broadcast::channel('TestTake.{testTakeUuid}', function () {
    return true;
});
Broadcast::channel('TestParticipant.{testParticipantUuid}', function ($user, $testParticipantUuid) {
    $testParticipantUserUuid = \tcCore\TestParticipant::whereUuid($testParticipantUuid)->value('user_id');
    return $testParticipantUserUuid === $user->getKey();
});
Broadcast::channel('User.{userUuid}', function ($user) {
    return true;
});

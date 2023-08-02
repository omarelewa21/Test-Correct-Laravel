<?php

use Illuminate\Support\Facades\Broadcast;
use tcCore\Scopes\ArchivedScope;

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

Broadcast::channel('presence-TestTake-CoLearning.{testTakeUuid}', function ($user, $testTakeUuid) {

    $testParticipantUuid = \tcCore\TestParticipant::where('user_id', '=', $user->getKey())
        ->whereIn('test_take_id', \tcCore\TestTake::withoutGlobalScope(ArchivedScope::class)
            ->whereUuid($testTakeUuid)
            ->select('id')
        )->first()
        ?->uuid;

    return [
        'user_uuid'            => $user->uuid,
        'testparticipant_uuid' => $testParticipantUuid,
        'student'              => $user->isA('Student')
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

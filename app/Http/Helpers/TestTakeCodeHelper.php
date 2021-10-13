<?php

namespace tcCore\Http\Helpers;

use tcCore\Lib\TestParticipant\Factory as ParticipantFactory;
use tcCore\Lib\User\Factory as UserFactory;
use tcCore\SchoolClass;
use tcCore\TestParticipant;
use tcCore\TestTakeCode;
use tcCore\TestTakeStatus;
use tcCore\User;

class TestTakeCodeHelper extends BaseHelper
{

    public function __construct()
    {

    }

    public function getTestTakeCodeIfExists($testTakeCode)
    {
        $code = is_array($testTakeCode) ? implode('', $testTakeCode) : $testTakeCode;

        return TestTakeCode::whereCode($code)->first();
    }

    public function createUserByTestTakeCode($guestData, TestTakeCode $testTakeCode)
    {
        $guestData += [
            'guest'              => true,
            'school_location_id' => $testTakeCode->getSchoolLocationFromTestTakeCode(),
            'test_take_code_id'  => $testTakeCode->getKey(),
            'user_roles'         => [3]
        ];

        $userFactory = new UserFactory(new User());
        $user = $userFactory->generate($guestData);

        $user->setAttribute('username', sprintf(User::GUEST_ACCOUNT_EMAIL_PATTERN, $user->getKey()));
        $user->save();

        return $user;
    }

    public function createTestParticipantForGuestUserByTestTakeCode(User $guestUser, TestTakeCode $testTakeCode)
    {
        $testTake = $testTakeCode->testTake;

        $schoolClass = SchoolClass::whereTestTakeId($testTake->getKey())->first();
        if (!$schoolClass) {
            $schoolClass = SchoolClass::createGuestClassForTestTake($testTake);
        }

        $participantData = [
            'test_take_id'            => $testTake->getKey(),
            'user_id'                 => $guestUser->getKey(),
            'test_take_status_id'     => $this->getTestTakeStatusIdForNewParticipant($testTake),
            'allow_inbrowser_testing' => $testTake->allow_inbrowser_testing,
            'school_class_id'         => $schoolClass->getKey(),
        ];

        $testParticipantFactory = new ParticipantFactory(new TestParticipant());
        $participant = $testParticipantFactory->generate($participantData);

        return $participant;
    }

    private function getTestTakeStatusIdForNewParticipant($testTake): int
    {
        return $testTake->test_take_status_id == TestTakeStatus::STATUS_PLANNED ? TestTakeStatus::STATUS_PLANNED : TestTakeStatus::STATUS_TEST_NOT_TAKEN;
    }
}
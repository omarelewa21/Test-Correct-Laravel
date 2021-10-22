<?php

namespace tcCore\Http\Helpers;

use Illuminate\Support\Facades\Auth;
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
            'available_for_guests'    => true,
        ];

        $testParticipantFactory = new ParticipantFactory(new TestParticipant());
        $participant = $testParticipantFactory->generate($participantData);

        return $participant;
    }

    private function getTestTakeStatusIdForNewParticipant($testTake): int
    {
        return $testTake->test_take_status_id == TestTakeStatus::STATUS_PLANNED ? TestTakeStatus::STATUS_PLANNED : TestTakeStatus::STATUS_TEST_NOT_TAKEN;
    }

    public function handleGuestLogin($guestData, $testTakeCode)
    {
        $testTakeStage = $testTakeCode->testTake->determineTestTakeStage();

        if ($testTakeStage === 'planned') {
            $this->handleStagePlanned($guestData, $testTakeCode);
        }

        if ($testTakeStage === 'discuss') {
            $this->handleStageDiscuss($testTakeCode);
        }

        if ($testTakeStage === 'review') {
            $this->handleStageReview($testTakeCode);
        }

        if ($testTakeStage === 'graded') {
            $this->handleStageGraded($testTakeCode);
        }

        return $this->errors;
    }

    private function handleStagePlanned($guestData, $testTakeCode)
    {
        $nameInAlreadyInUse = $this->isNameInAlreadyInUse($testTakeCode, $guestData);

        if ($nameInAlreadyInUse) {
            $this->addError('name_already_in_use');
            return false;
        }
        $guestUser = $this->createUserByTestTakeCode($guestData, $testTakeCode);
        $guestParticipant = $this->createTestParticipantForGuestUserByTestTakeCode($guestUser, $testTakeCode);
        if ($guestUser && $guestParticipant) {
            Auth::login($guestUser);
            return redirect()->intended(route('student.waiting-room', ['take' => $testTakeCode->testTake->uuid]));
        }
        return false;
    }

    private function handleStageDiscuss($testTakeCode)
    {
        session()->put('guest_take', $testTakeCode->testTake->uuid);
        return redirect(route('guest-choice', ['take' => $testTakeCode->testTake->uuid]));
    }

    private function handleStageReview($testTakeCode)
    {
        session()->put('guest_take', $testTakeCode->testTake->uuid);
        return redirect(route('guest-choice', ['take' => $testTakeCode->testTake->uuid]));
    }

    private function handleStageGraded($testTakeCode)
    {
        session()->put('guest_take', $testTakeCode->testTake->uuid);
        return redirect(route('guest-graded-overview', ['take' => $testTakeCode->testTake->uuid]));
    }

    /**
     * @param $testTakeCode
     * @param $guestData
     * @return bool
     */
    private function isNameInAlreadyInUse($testTakeCode, $guestData): bool
    {
        $existingGuestNames = User::guests()
            ->select(['name_first', 'name'])
            ->whereTestTakeCodeId($testTakeCode->getKey())
            ->get();

        $nameInAlreadyInUse = false;
        $existingGuestNames->each(function ($userNames) use ($guestData, &$nameInAlreadyInUse) {
            if ($userNames->name_first == $guestData['name_first'] && $userNames->name == $guestData['name']) {
                $nameInAlreadyInUse = true;
            }
        });

        return $nameInAlreadyInUse;
    }
}
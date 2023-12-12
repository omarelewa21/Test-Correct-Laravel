<?php

namespace tcCore\Http\Helpers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use tcCore\Lib\TestParticipant\Factory as ParticipantFactory;
use tcCore\Lib\User\Factory as UserFactory;
use tcCore\SchoolClass;
use tcCore\TestKind;
use tcCore\TestParticipant;
use tcCore\TestTakeCode;
use tcCore\TestTakeStatus;
use tcCore\User;

class TestTakeCodeHelper extends BaseHelper
{
    public static function getTestTakeCodeModelFromCode($testTakeCode)
    {
        $code = is_array($testTakeCode) ? implode('', $testTakeCode) : $testTakeCode;
        return TestTakeCode::whereCode($code)->first();
    }

    public function checkAccessViaTestTakeCodeIfExists($testTakeCode)
    {

        $foundTestTakeCode = self::getTestTakeCodeModelFromCode($testTakeCode);
        return ($foundTestTakeCode
            && $foundTestTakeCode->testTake
            && $foundTestTakeCode->testTake->test
            && (
                Auth::user()->isA('teacher')
                ||
                (
                    Auth::user()->isA('student')
                    &&
                    (
                        ( // fail if not an assignment and doesn't start today
                            $foundTestTakeCode->testTake->test->test_kind_id !== TestKind::ASSIGNMENT_TYPE
                            && $foundTestTakeCode->testTake->time_start != Carbon::today()
                        )
                        || ( // fail if it is an assignment, and the time_start is later than today (starts later than today) or the time_end smaller than today (ends earlier than today)
                            $foundTestTakeCode->testTake->test->test_kind_id == TestKind::ASSIGNMENT_TYPE
                            && (
                                $foundTestTakeCode->testTake->time_start > Carbon::today()
                                || $foundTestTakeCode->testTake->time_end < Carbon::today()
                            )

                        )
                    )
                )
            )
        );
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
        $user->setAttribute('send_welcome_email',1);
        $user->save();

        return $user;
    }

    public function createTestParticipantForGuestUserByTestTakeCode(User $guestUser, TestTakeCode $testTakeCode)
    {
        $testTake = $testTakeCode->testTake;

        $schoolClass = SchoolClass::whereTestTakeId($testTake->getKey())->first();
        if (!$schoolClass) {
            if(null === ActingAsHelper::getInstance()->getUser()){
                ActingAsHelper::getInstance()->setUser($guestUser);
            }
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
        $nameIsAlreadyInUse = $this->isNameAlreadyInUse($testTakeCode, $guestData);

        if ($testTakeStage === 'planned') {
            if ($nameIsAlreadyInUse) {
                $this->addError('name_already_in_use');
            } else {
                $this->handleStagePlanned($guestData, $testTakeCode);
            }
        }

        if ($nameIsAlreadyInUse) {
            $this->setSessionVariables($testTakeCode, $guestData);

            if ($testTakeStage === 'discuss') {
                $this->handleStageDiscuss($testTakeCode);
            }

            if ($testTakeStage === 'review') {
                $this->handleStageReview($testTakeCode);
            }

            if ($testTakeStage === 'graded') {
                $this->handleStageGraded($testTakeCode);
            }
        } else {
            $this->addError('user_not_found_for_test_code');
        }

        if (empty($this->errors)) {
            $this->addError('test_take_not_in_valid_stage');
        }

        return $this->errors;
    }

    private function handleStagePlanned($guestData, $testTakeCode)
    {
        $guestUser = $this->createUserByTestTakeCode($guestData, $testTakeCode);
        $guestParticipant = $this->createTestParticipantForGuestUserByTestTakeCode($guestUser, $testTakeCode);
        if ($guestUser && $guestParticipant) {
            Auth::login($guestUser);
            BaseHelper::doLoginProcedure();
            return redirect()->intended(route('student.waiting-room', ['take' => $testTakeCode->testTake->uuid]));
        }
        return false;
    }

    private function handleStageDiscuss($testTakeCode)
    {
        return redirect(route('guest-choice', ['take' => $testTakeCode->testTake->uuid]));
    }

    private function handleStageReview($testTakeCode)
    {
        return redirect(route('guest-choice', ['take' => $testTakeCode->testTake->uuid]));
    }

    private function handleStageGraded($testTakeCode)
    {
        if ($testTakeCode->rating_visible_expiration->lt(Carbon::now())) {
            return $this->addError('rating_visible_expired');
        }

        return redirect(route('guest-graded-overview', ['take' => $testTakeCode->testTake->uuid]));
    }

    /**
     * @param $testTakeCode
     * @param $guestData
     * @return bool
     */
    private function isNameAlreadyInUse($testTakeCode, $guestData): bool
    {
        $existingGuestNames = User::guests()
            ->select(['name_first', 'name', 'name_suffix'])
            ->whereTestTakeCodeId($testTakeCode->getKey())
            ->get();

        $nameAlreadyInUse = false;
        $existingGuestNames->each(function ($userNames) use ($guestData, &$nameAlreadyInUse) {
            if (strtolower($userNames->name_first) == strtolower($guestData['name_first'])
                && strtolower($userNames->name) == strtolower($guestData['name'])
                && strtolower($userNames->name_suffix) == strtolower($guestData['name_suffix'])) {
                $nameAlreadyInUse = true;
            }
        });

        return $nameAlreadyInUse;
    }

    /**
     * @param $testTakeCode
     * @param $guestData
     */
    private function setSessionVariables($testTakeCode, $guestData)
    {
        session()->put('guest_data', $guestData);
        session()->put('guest_take', $testTakeCode->testTake->uuid);
    }
}
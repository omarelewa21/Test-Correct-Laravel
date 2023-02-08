<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Composer\DependencyResolver\Request;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Benchmark;
use Illuminate\Support\Facades\DB;
use tcCore\AnswerRating;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeDiscussed;
use tcCore\FactoryScenarios\FactoryScenarioTestTestWithTwoQuestions;
use tcCore\Http\Helpers\CoLearningHelper;
use tcCore\TestParticipant;
use tcCore\TestTakeStatus;
use tcCore\User;
use Tests\TestCase;

class CoLearningHelperTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Assert CoLearningHelper gets the correct amount of answers the student has to rate
     *  for the TestTake => discussing question
     * @test
     */
    public function canGetTestParticipantAnswersToRateForDiscussingQuestion()
    {
        $this->markTestIncomplete('TODO');
    }

    /**
     * Assert CoLearningHelper gets the correct amount of answers the student has rated
     *  for the TestTake => discussing question
     * @test
     */
    public function canGetTestParticipantRatedAnswersForDiscussingQuestion()
    {
        $this->markTestIncomplete('TODO');
    }

    /**
     * Assert CoLearningHelper can determine which students are active and which are not
     * @test
     */
    public function canGetTestParticipantActiveStatus()
    {
        $testTake = FactoryScenarioTestTakeDiscussed::createTestTake(
            user: $user = User::find(1486),
            test: $test = FactoryScenarioTestTestWithTwoQuestions::createTest('abnormalities-test', $user)
        );

        $testPartcipants = TestParticipant::where('test_take_id', '=', $testTake->getKey())->orderBy('user_id')->get();
        if ($testPartcipants->count() < 3) {
            throw new \Exception('Test needs at least 3 testParticipants to function');
        }
        $testPartcipants->each(function ($testParticipant, $key) {
            $key === 0
                ? $testParticipant->heartbeat_at = Carbon::now()
                : ($key === 1
                ? $testParticipant->heartbeat_at = Carbon::now()->subSeconds(15)
                : ($key === 2
                    ? $testParticipant->heartbeat_at = Carbon::now()->subMinute()
                    : $testParticipant->heartbeat_at = null
                )
            );
            $testParticipant->save();
        });

        $testParticipantsData = CoLearningHelper::getTestParticipantsWithStatusAndAbnormalities($testTake->getKey(), $testTake->discussing_question_id)->sortBy('user_id')->values();


        $this->assertEquals(1, $testParticipantsData->offsetGet(0)->active);
        $this->assertEquals(1, $testParticipantsData->offsetGet(1)->active);
        $this->assertEquals(0, $testParticipantsData->offsetGet(2)->active);

    }


    /**
     * This test sets the answerRatings of one testParticipant to a different value from all the other AnswerRatings (all three types: STUDENT, TEACHER, SYSTEM)
     * It asserts that the user has just as many abnormalities as possible answerRatings.
     * @test
     */
    public function TestParticipantWithOnlyDifferentRatingsAsTheRestHasEqualAmountOfAbnormalitiesAsAnswerRatings()
    {
        $testTake = FactoryScenarioTestTakeDiscussed::createTestTake(
            user: $user = User::find(1486),
            test: $test = FactoryScenarioTestTestWithTwoQuestions::createTest('abnormalities-test', $user)
        );

        $answerRatings = AnswerRating::where('test_take_id', '=', $testTake->getKey())->orderBy('answer_id')->get();

        //change all but one student to the same rating:
        $firstUserId = $answerRatings
            ->where('user_id', '<>', null)
            ->first()
            ->user_id;
        $answerRatings->each(function ($answerRating) use ($firstUserId) {
            if ($answerRating->user_id === $firstUserId) {
                $answerRating->rating = 1;
                $answerRating->save();
                return;
            }
            $answerRating->rating = 5;
            $answerRating->save();
        });

        $testParticipantsData = CoLearningHelper::getTestParticipantsWithStatusAndAbnormalities($testTake->getKey(), $testTake->discussing_question_id);

        $firstUserAnswerRatingsCount = $answerRatings->filter(fn($answerRating) => $answerRating->user_id === $firstUserId)->count();
        $firstUserAbnormalities = (int)$testParticipantsData->where('user_id', '=', $firstUserId)->first()->abnormalities;

        $this->assertEquals($firstUserAnswerRatingsCount, $firstUserAbnormalities);
    }

    /**
     * This test sets the answerRatings of one testParticipant to a different value from all the other AnswerRatings (all three types: STUDENT, TEACHER, SYSTEM)
     * It asserts that the user has just as many abnormalities as possible answerRatings.
     * @test
     */
    public function TestParticipantWithOnlyTheSameRatingsAsTheRestHasZeroAbnormalities()
    {
        $testTake = FactoryScenarioTestTakeDiscussed::createTestTake(
            user: $user = User::find(1486),
            test: $test = FactoryScenarioTestTestWithTwoQuestions::createTest('abnormalities-test', $user)
        );

        $answerRatings = AnswerRating::where('test_take_id', '=', $testTake->getKey())->orderBy('answer_id')->get();

        $firstUserId = $answerRatings
            ->where('user_id', '<>', null)
            ->first()
            ->user_id;

        //change all student AnswerRatings to the same rating:
        $answerRatings->each(function ($answerRating) {
            $answerRating->rating = 5;
            $answerRating->save();
        });

        $testParticipantsData = CoLearningHelper::getTestParticipantsWithStatusAndAbnormalities($testTake->getKey(), $testTake->discussing_question_id);

        $firstUserAnswerRatingsCount = $answerRatings->filter(fn($answerRating) => $answerRating->user_id === $firstUserId)->count();
        $firstUserAbnormalities = (int)$testParticipantsData->where('user_id', '=', $firstUserId)->first()->abnormalities;

        $this->assertGreaterThan(0, $firstUserAnswerRatingsCount);
        $this->assertEquals(0, $firstUserAbnormalities);
    }

    /**
     * This test creates SYSTEM answerRatings for one of the testParticipants, that are NOT equal to the ratings by this testParticipant (equals abnormalities)
     * It asserts that the user has just as many abnormalities as possible answerRatings.
     * @test
     */
    public function TestParticipantWithOnlyDifferentRatingsAsTheSystemRatingsHasEqualAmountOfAbnormalitiesAsAnswerRatings()
    {
        $testTake = FactoryScenarioTestTakeDiscussed::createTestTake(
            user: $user = User::find(1486),
            test: $test = FactoryScenarioTestTestWithTwoQuestions::createTest('abnormalities-test', $user)
        );
        $testTake->update([
            'test_take_status_id' => TestTakeStatus::STATUS_DISCUSSING,
        ]);

        $answerRatings = AnswerRating::where('test_take_id', '=', $testTake->getKey())->orderBy('answer_id')->get();

        $firstUserId = null;
        $firstUserAnswerIds = $answerRatings->reduce(function ($carry, $answerRating) use (&$firstUserId) {
            if ($firstUserId === null) {
                $firstUserId = $answerRating->user_id;
            }
            if ($answerRating->user_id === $firstUserId) {
                $carry[] = $answerRating->answer_id;
            }
            return $carry;
        }, []);

        //delete system and Teacher ratings
        $answerRatings->each(function ($answerRating) {
            if ($answerRating->type !== 'STUDENT') {
                $answerRating->forceDelete();
                return;
            }
            $answerRating->rating = 1;
            $answerRating->save();
        });

        //create SYSTEM answer_ratings
        collect($firstUserAnswerIds)->each(function ($answerId) use ($testTake) {
            (new AnswerRating([
                'answer_id'    => $answerId,
                'test_take_id' => $testTake->getKey(),
                'type'         => 'SYSTEM',
                'rating'       => 5,
            ]))->save();
        });

        $testParticipantsData = CoLearningHelper::getTestParticipantsWithStatusAndAbnormalities($testTake->getKey(), $testTake->discussing_question_id);
        $updatedAnswerRatings = AnswerRating::where('test_take_id', '=', $testTake->getKey())->orderBy('answer_id')->get();

        $firstUserAnswerRatingsCount = $updatedAnswerRatings
            ->filter(fn($answerRating) => $answerRating->user_id === $firstUserId)
            ->count();
        $firstUserAbnormalities = (int)$testParticipantsData
            ->where('user_id', '=', $firstUserId)
            ->first()
            ->abnormalities;

        $createdSystemAnswerRatingsForFirstUserAnswers_count = $updatedAnswerRatings->where('type', 'SYSTEM')->count();

        $this->assertEquals($firstUserAnswerRatingsCount, $createdSystemAnswerRatingsForFirstUserAnswers_count);
        $this->assertEquals($firstUserAnswerRatingsCount, $firstUserAbnormalities);
    }

    /**
     * This test creates TEACHER answerRatings ratings for one of the testParticipants, that are NOT equal to the ratings by this testParticipant (eg. abnormalities)
     * It asserts that the user has just as many abnormalities as possible answerRatings.
     * @test
     */
    public function TestParticipantWithOnlyDifferentRatingsAsTheTeacherRatingsHasEqualAmountOfAbnormalitiesAsAnswerRatings()
    {
        $testTake = FactoryScenarioTestTakeDiscussed::createTestTake(
            user: $user = User::find(1486),
            test: $test = FactoryScenarioTestTestWithTwoQuestions::createTest('abnormalities-test', $user)
        );
        $testTake->update([
            'test_take_status_id' => TestTakeStatus::STATUS_DISCUSSING,
        ]);

        $answerRatings = AnswerRating::where('test_take_id', '=', $testTake->getKey())
            ->orderBy('answer_id')
            ->get();

        $firstUserId = null;
        $firstUserAnswerIds = $answerRatings->reduce(function ($carry, $answerRating) use (&$firstUserId) {
            if ($firstUserId === null) {
                $firstUserId = $answerRating->user_id;
            }
            if ($answerRating->user_id === $firstUserId) {
                $carry[] = $answerRating->answer_id;
            }
            return $carry;
        }, []);

        //delete system and Teacher ratings
        $answerRatings->each(function ($answerRating) {
            if ($answerRating->type !== 'STUDENT') {
                $answerRating->forceDelete();
                return;
            }
            $answerRating->rating = 1;
            $answerRating->save();
        });

        //create TEACHER answer_ratings
        collect($firstUserAnswerIds)->each(function ($answerId) use ($testTake) {
            (new AnswerRating([
                'answer_id'    => $answerId,
                'test_take_id' => $testTake->getKey(),
                'type'         => 'TEACHER',
                'rating'       => 5,
            ]))->save();
        });

        $testParticipantsData = CoLearningHelper::getTestParticipantsWithStatusAndAbnormalities($testTake->getKey(), $testTake->discussing_question_id);
        $updatedAnswerRatings = AnswerRating::where('test_take_id', '=', $testTake->getKey())->orderBy('answer_id')->get();

        $firstUserAnswerRatingsCount = $updatedAnswerRatings
            ->filter(fn($answerRating) => $answerRating->user_id === $firstUserId)
            ->count();
        $firstUserAbnormalities = (int)$testParticipantsData
            ->where('user_id', '=', $firstUserId)
            ->first()
            ->abnormalities;

        $createdTeacherAnswerRatingsForFirstUserAnswers_count = $updatedAnswerRatings->where('type', 'TEACHER')->count();

        $this->assertEquals($firstUserAnswerRatingsCount, $createdTeacherAnswerRatingsForFirstUserAnswers_count);
        $this->assertEquals($firstUserAnswerRatingsCount, $firstUserAbnormalities);
    }

    /**
     * This test creates SYSTEM and TEACHER answerRatings for one of the testParticipants,
     * the SYSTEM ratings are NOT the same as the student, but:
     *  the TEACHER ratings ARE the same as the student rating, this means NO (zero) abnormalities
     * It asserts that the user has 0 abnormalities. because the TEACHER ratings overrule the SYSTEM ratings
     * @test
     */
    public function TestParticipantHasZeroAbnormalitiesWithCorrectTeacherAnswerRating()
    {
        $testTake = FactoryScenarioTestTakeDiscussed::createTestTake(
            user: $user = User::find(1486),
            test: $test = FactoryScenarioTestTestWithTwoQuestions::createTest('abnormalities-test', $user)
        );
        $testTake->update([
            'test_take_status_id' => TestTakeStatus::STATUS_DISCUSSING,
        ]);

        $answerRatings = AnswerRating::where('test_take_id', '=', $testTake->getKey())
            ->orderBy('answer_id')
            ->get();

        $firstUserId = null;
        $firstUserAnswerIds = $answerRatings->reduce(function ($carry, $answerRating) use (&$firstUserId) {
            if ($firstUserId === null) {
                $firstUserId = $answerRating->user_id;
            }
            if ($answerRating->user_id === $firstUserId) {
                $carry[] = $answerRating->answer_id;
            }
            return $carry;
        }, []);

        //delete existing system and Teacher ratings
        $answerRatings->each(function ($answerRating) {
            if ($answerRating->type !== 'STUDENT') {
                $answerRating->forceDelete();
                return;
            }
            //set all student ratings to 1
            $answerRating->rating = 1;
            $answerRating->save();
        });

        //create TEACHER and SYSTEM answer_ratings
        collect($firstUserAnswerIds)->each(function ($answerId) use ($testTake) {
            AnswerRating::insert([
                [
                    'answer_id'    => $answerId,
                    'test_take_id' => $testTake->getKey(),
                    'type'         => 'SYSTEM',
                    'rating'       => 5, //not equal to STUDENT rating
                ], [
                    'answer_id'    => $answerId,
                    'test_take_id' => $testTake->getKey(),
                    'type'         => 'TEACHER',
                    'rating'       => 1, //equal to STUDENT rating
                ],
            ]);
        });

        $testParticipantsData = CoLearningHelper::getTestParticipantsWithStatusAndAbnormalities($testTake->getKey(), $testTake->discussing_question_id);
        $updatedAnswerRatings = AnswerRating::where('test_take_id', '=', $testTake->getKey())->orderBy('answer_id')->get();

        $firstUserAnswerRatingsCount = $updatedAnswerRatings
            ->filter(fn($answerRating) => $answerRating->user_id === $firstUserId)
            ->count();
        $firstUserAbnormalities = (int)$testParticipantsData
            ->where('user_id', '=', $firstUserId)
            ->first()
            ->abnormalities;


        //assert TEACHER and SYSTEM AnswerRatings are created
        $this->assertEquals(
            expected: $firstUserAnswerRatingsCount,
            actual: $updatedAnswerRatings->where('type', 'TEACHER')->count()
        );
        $this->assertEquals(
            expected: $firstUserAnswerRatingsCount,
            actual: $updatedAnswerRatings->where('type', 'SYSTEM')->count()
        );

        //assert first user answerRatings contains the Teacher rating "1.0" but not the SYSTEM rating "5.0"
        $this->assertContains(
            needle: $updatedAnswerRatings->where('type', 'TEACHER')->first()->rating, //"1.0"
            haystack: $updatedAnswerRatings->where('user_id', '=', $firstUserId)->map->rating
        );
        $this->assertNotContains(
            needle: $updatedAnswerRatings->where('type', 'SYSTEM')->first()->rating, //"5.0"
            haystack: $updatedAnswerRatings->where('user_id', '=', $firstUserId)->map->rating
        );

        //assert there are no abnormalities for the user, because all its ratings are the same as the TEACHER
        $this->assertEquals(
            expected: 0,
            actual: $firstUserAbnormalities
        );
    }


    /** @test */
    public function benchmarkTestTakeControllerMethodVsCoLearningHelper()
    {
        $testTake = FactoryScenarioTestTakeDiscussed::createTestTake(
            user: $user = User::find(1486),
            test: $test = FactoryScenarioTestTestWithTwoQuestions::createTest('abnormalities-test', $user)
        );
        auth()->login($user);
        $testTake->update([
            'test_take_status_id' => TestTakeStatus::STATUS_DISCUSSING,
        ]);

        $testTakeId = $testTake->getKey();

        $request = new \Illuminate\Http\Request([
            'with' => ['participantStatus', 'discussingQuestion'],
        ]);
        $benchmark = [];
        $result1 = null;
        $result2 = null;
        //end of set-up


        DB::enableQueryLog();

        $benchmark['TestTakesController']['time'] = Benchmark::measure(function () use (&$result1, $testTake, $request) {
            return $result1 = CoLearningHelper::getTestParticipantsWithStatusOldController(
                testTake: $testTake,
                request: $request,
            );
        });

        $this->handleQueryLog($benchmark, 'TestTakesController');

        $benchmark['CoLearningHelper']['time'] = Benchmark::measure(function () use (&$result2, $testTakeId, $user, $testTake) {
            return $result2 = CoLearningHelper::getTestParticipantsWithStatusAndAbnormalities($testTakeId, $testTake->discussing_question_id);
//            return $result2 = CoLearningHelper::getTestParticipantsWithStatus($testTakeId, $testTake->discussing_question_id);
        });

        $this->handleQueryLog($benchmark, 'CoLearningHelper');

        collect($benchmark)
            ->each(function ($item, $key) {
                echo sprintf("%s needed %s milliseconds to generate data\n", $key, $item['time']);
            });

        $this->assertLessThan($benchmark['TestTakesController']['time'], $benchmark['CoLearningHelper']['time']);
        $this->assertLessThan($benchmark['TestTakesController']['query-time'], $benchmark['CoLearningHelper']['query-time']);
        $this->assertLessThan($benchmark['TestTakesController']['queries'], $benchmark['CoLearningHelper']['queries']);
    }

    protected function handleQueryLog(&$benchmark, $index)
    {
        $queryLog = collect(DB::getQueryLog());

        $benchmark[$index]['query-time'] = $queryLog->sum('time');
        $benchmark[$index]['queries'] = $queryLog->count();

        DB::flushQueryLog();
    }
}
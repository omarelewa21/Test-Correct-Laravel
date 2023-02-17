<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Benchmark;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use tcCore\AnswerRating;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeDiscussed;
use tcCore\FactoryScenarios\FactoryScenarioTestTestWithTwoQuestions;
use tcCore\Http\Controllers\TestTakesController;
use tcCore\Http\Helpers\CoLearningHelper;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\TestTakeStatus;
use tcCore\User;
use Tests\TestCase;

class CoLearningHelperTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function canGetAnswersToRateAndAnswerRatedWhenThereAreSoftDeletedAnswersOrAnswerRatings()
    {
        $testTake = $this->setUpTestTake();
        $answerRatings = $this->getAnswerRatings($testTake);
        $firstUserId = $answerRatings
            ->where('user_id', '<>', null)
            ->first()
            ->user_id;

        $answerRatingCopies = collect();

        //save different rating to first student answerRatings
        $answerRatings
            ->where('user_id', '=', $firstUserId)
            ->each(function ($answerRating) use (&$answerRatingCopies) {
                $answerRating->rating = 1;
                $answerRating->save();
                $answerRatingCopies[] = $answerRating->toArray();
            });

        //delete first user answerRatings
        $answerRatings
            ->where('user_id', '=', $firstUserId)
            ->each(function ($answerRating) {
                $answerRating->delete();
            });

        //create new answerRatings for the first user
        $answerRatingCopies->each(function ($answerRatingData) {
            unset($answerRatingData['id']);
            unset($answerRatingData['answer']);
            AnswerRating::create($answerRatingData)->delete();
            return AnswerRating::create($answerRatingData);
        });

        $testParticipantsData = CoLearningHelper::getTestParticipantsWithStatusAndAbnormalities($testTake->getKey(), $testTake->discussing_question_id)
            ->sortBy('user_id')->values();

        foreach ($testParticipantsData as $testParticipant) {
            $this->assertLessThanOrEqual(2, $testParticipant->answer_rated);
            $this->assertLessThanOrEqual(2, $testParticipant->answer_to_rate);
        }
    }

    /**
     * Assert CoLearningHelper gets the correct amount of answers the student has to rate
     *  for the TestTake => discussing question
     * @test
     */
    public function canGetTestParticipantAnswersToRateForDiscussingQuestion()
    {
        $testTake = $this->setUpTestTake();

        //assert all answerRatings Answers are answered
        $answerRatings = $this->getAnswerRatings($testTake);

        $answers = $answerRatings->unique->answer->map->answer;

        $allAnswersAreAnswered = $answers->reduce(function ($carry, $answer) {
            if ($answer->isAnswered) {
                return $carry;
            }
            return false;
        }, true);
        $this->assertEquals(true, $allAnswersAreAnswered);


        //change change one of the answers of the discussing question to unanswered for the first user
        $firstUserId = $answerRatings->where('user_id', '<>', null)->sortBy('user_id')->map->user_id->first();

        $answer = $answerRatings->where('user_id', '=', $firstUserId)
            ->map
            ->answer
            ->where('question_id', '=', $testTake->discussing_question_id)
            ->first();
        $answer->json = null;
        $answer->done = 0;
        $answer->save();


        //assert result of CoLearningHelper returns the same value;
        $testParticipantsData = CoLearningHelper::getTestParticipantsWithStatusAndAbnormalities($testTake->getKey(), $testTake->discussing_question_id)->sortBy('user_id')->values();

        $maxAnswerToRate = $testParticipantsData->max('answer_to_rate');

        //assert that first testParticipant has to rate less answers than the rest (at least one other student will also have to rate only 1 Answer, therefore compare with max value)
        $testParticipantsData->each(function ($testParticipant) use ($firstUserId, $maxAnswerToRate) {
            if ($testParticipant->user_id === $firstUserId) {
                $this->assertLessThan($maxAnswerToRate /* 2 */, $testParticipant->answer_to_rate /* 1 */);
                return;
            }
        });
    }

    /**
     * Assert CoLearningHelper gets the correct amount of answers the student has rated
     *  for the TestTake => discussing question
     * @test
     */
    public function canGetTestParticipantRatedAnswersForDiscussingQuestion()
    {
        $testTake = $this->setUpTestTake();

        //assert all answerRatings ratings are not null
        $answerRatings = $this->getAnswerRatings($testTake);

        $notRatedAnswerRatingsCount = $answerRatings->filter(fn($ar) => $ar->rating === null)->count();
        $ratedAnswerRatingsCount = $answerRatings->filter(fn($ar) => $ar->rating !== null)->count();
        $this->assertEquals(0, $notRatedAnswerRatingsCount);
        $this->assertGreaterThan(0, $ratedAnswerRatingsCount);

        //change rating of one users answerRatings to null
        $firstUserId = $answerRatings->where('user_id', '<>', null)->sortBy('user_id')->first()->user_id;
        $temp = $answerRatings->filter(fn($ar) => $ar->user_id === $firstUserId)->each(fn($ar) => $ar->update(['rating' => null]));

        //assert result of CoLearningHelper returns the same value;
        $testParticipantsData = CoLearningHelper::getTestParticipantsWithStatusAndAbnormalities($testTake->getKey(), $testTake->discussing_question_id)->sortBy('user_id')->values();
        $updatedAnswerRatings = $this->getAnswerRatings($testTake);

        //assert that only the first testParticipant has not rated their AnswersRatings and the rest have
        $testParticipantsData->each(function ($testParticipant) use ($firstUserId) {
            if ($testParticipant->user_id === $firstUserId) {
                $this->assertEquals(0, $testParticipant->answer_rated);
                return;
            }
            $this->assertGreaterThan(0, $testParticipant->answer_rated);
            $this->assertEquals(2, $testParticipant->answer_rated); //currently (feb-2023) students have to rate 2 answerRatings per Question
        });
    }

    /**
     * Assert CoLearningHelper can determine which students are active and which are not
     * @test
     */
    public function canGetTestParticipantActiveStatus()
    {
        $testTake = $this->setUpTestTake();

        $testPartcipants = TestParticipant::where('test_take_id', '=', $testTake->getKey())->orderBy('user_id')->get();
        if ($testPartcipants->count() < 3) {
            $this->markTestIncomplete(__METHOD__ . ': Test needs at least 3 testParticipants to function');
        }
        $testPartcipants->each(function ($testParticipant, $key) {
            switch ($key) {
                case 0:
                    $testParticipant->heartbeat_at = Carbon::now();
                    break;
                case 1:
                    $testParticipant->heartbeat_at = Carbon::now()->subSeconds(15);
                    break;
                case 2:
                    $testParticipant->heartbeat_at = Carbon::now()->subMinute();
                    break;
                default:
                    $testParticipant->heartbeat_at = null;
                    break;
            }
            $testParticipant->save();
        });

        $testParticipantsData = CoLearningHelper::getTestParticipantsWithStatusAndAbnormalities($testTake->getKey(), $testTake->discussing_question_id)->sortBy('user_id')->values();

        $testParticipantThatwasActive0secondsAgo = $testParticipantsData->offsetGet(0);
        $testParticipantThatwasActive15secondsAgo = $testParticipantsData->offsetGet(1);
        $testParticipantThatwasActive60secondsAgo = $testParticipantsData->offsetGet(2);

        $this->assertEquals(true, $testParticipantThatwasActive0secondsAgo->active);
        $this->assertEquals(true, $testParticipantThatwasActive15secondsAgo->active);
        $this->assertEquals(false, $testParticipantThatwasActive60secondsAgo->active);

    }


    /**
     * This test sets the answerRatings of one testParticipant to a different value from all the other AnswerRatings (all three types: STUDENT, TEACHER, SYSTEM)
     * It asserts that the user has just as many abnormalities as possible answerRatings.
     * @test
     */
    public function TestParticipantWithOnlyDifferentRatingsAsTheRestHasEqualAmountOfAbnormalitiesAsAnswerRatings()
    {
        $testTake = $this->setUpTestTake();

        $answerRatings = $this->getAnswerRatings($testTake);

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
        $testTake = $this->setUpTestTake();

        $answerRatings = $this->getAnswerRatings($testTake);

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
     * Assert that soft deleted AnswerRatings don't create problems / don't cause unexpected abnormalities
     * @test
     */
    public function TestParticipantHasZeroAbnormalitiesWhileHavingAbnormalitiesInSoftDeletedAnswerRatings()
    {
        $testTake = $this->setUpTestTake();

        $answerRatings = $this->getAnswerRatings($testTake);

        $firstUserId = $answerRatings
            ->where('user_id', '<>', null)
            ->first()
            ->user_id;

        $answerRatingCopies = collect();

        //save different rating to first student answerRatings
        $answerRatings
            ->where('user_id', '=', $firstUserId)
            ->each(function ($answerRating) use (&$answerRatingCopies) {
                $answerRating->rating = 1;
                $answerRating->save();
                $answerRatingCopies[] = $answerRating->toArray();
            });

        //delete first user answerRatings
        $answerRatings
            ->where('user_id', '=', $firstUserId)
            ->each(function ($answerRating) {
                $answerRating->delete();
            });

        //change all student AnswerRatings to the same rating:
        $answerRatings = $this->getAnswerRatings($testTake);
        $answerRatings->each(function ($answerRating) {
            $answerRating->rating = 5;
            $answerRating->save();
        });

        //create new answerRatings for the first user
        $answerRatingCopies->each(function ($answerRatingData) {
            unset($answerRatingData['id']);
            unset($answerRatingData['answer']);
            $answerRatingData['rating'] = 5;
            return AnswerRating::create($answerRatingData);
        });

        $testParticipantsData = CoLearningHelper::getTestParticipantsWithStatusAndAbnormalities($testTake->getKey(), $testTake->discussing_question_id);

        $answerRatings = $this->getAnswerRatings($testTake);

        $firstUserAnswerRatingsWithoutTrashedCount = $answerRatings->where('user_id', '=', $firstUserId)->count();
        $firstUserAnswerRatingsWithTrashedCount = $this->getAnswerRatings($testTake, true)->where('user_id', '=', $firstUserId)->count();

        $this->assertNotEquals($firstUserAnswerRatingsWithTrashedCount, $firstUserAnswerRatingsWithoutTrashedCount);

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
        $testTake = $this->setUpTestTake();

        $answerRatings = $this->getAnswerRatings($testTake);

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
                $answerRating->delete();
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
        $updatedAnswerRatings = $this->getAnswerRatings($testTake);

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
        $testTake = $this->setUpTestTake();

        $answerRatings = $this->getAnswerRatings($testTake);

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
                $answerRating->delete();
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

        $updatedAnswerRatings = $this->getAnswerRatings($testTake);

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
        $testTake = $this->setUpTestTake();

        $answerRatings = $this->getAnswerRatings($testTake);

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
                $answerRating->delete();
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
        $updatedAnswerRatings = $this->getAnswerRatings($testTake);

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


    /**
     * This test creates SYSTEM answerRatings for one of the testParticipants,
     * the SYSTEM ratings are the same as the student, but:
     *  the other students have different ratings.
     * It asserts that the user has 0 abnormalities. because the TEACHER ratings overrule the SYSTEM ratings and those overrule the student ratings
     * @test
     */
    public function TestParticipantHasZeroAbnormalitiesWithCorrectTeacherAnswerRatingButIncorrectStudentRatings()
    {
        $testTake = $this->setUpTestTake();

        $answerRatings = $this->getAnswerRatings($testTake);

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
        $answerRatings->each(function ($answerRating) use ($firstUserId) {
            if ($answerRating->type !== 'STUDENT') {
                $answerRating->delete();
                return;
            }
            if ($answerRating->user_id === $firstUserId) {
                //set first student ratings to the same as SYSTEM
                $answerRating->rating = 5;
                $answerRating->save();
                return;
            }
            //set all other student ratings to 1
            $answerRating->rating = 1;
            $answerRating->save();
        });

        //create SYSTEM answer_ratings
        $systemAnswerRatings = collect($firstUserAnswerIds)->reduce(function ($carry, $answerId) use ($testTake) {
            $carry[] = [
                'answer_id'    => $answerId,
                'test_take_id' => $testTake->getKey(),
                'type'         => 'SYSTEM',
                'rating'       => 5, //equal to first STUDENT rating, not equal to the rest
            ];
            return $carry;
        }, []);

        AnswerRating::insert($systemAnswerRatings);

        $testParticipantsData = CoLearningHelper::getTestParticipantsWithStatusAndAbnormalities($testTake->getKey(), $testTake->discussing_question_id);

        $updatedAnswerRatings = $this->getAnswerRatings($testTake);

        $firstUserAnswerRatingsCount = $updatedAnswerRatings
            ->filter(fn($answerRating) => $answerRating->user_id === $firstUserId)
            ->count();
        $firstUserAbnormalities = (int)$testParticipantsData
            ->where('user_id', '=', $firstUserId)
            ->first()
            ->abnormalities;


        //assert SYSTEM AnswerRatings are created
        $this->assertEquals(
            expected: $firstUserAnswerRatingsCount,
            actual: $updatedAnswerRatings->where('type', 'SYSTEM')->count()
        );

        //assert second user answerRatings dont contain the SYSTEM rating "5.0"
        $this->assertNotContains(
            needle: $updatedAnswerRatings->where('type', 'STUDENT')->where('user_id', '<>', $firstUserId)->first()->rating, //"1.0"
            haystack: $updatedAnswerRatings->where('user_id', '=', $firstUserId)->map->rating
        );
        //assert first user answerRatings contains the SYSTEM rating "5.0"
        $this->assertContains(
            needle: $updatedAnswerRatings->where('type', 'SYSTEM')->first()->rating, //"5.0"
            haystack: $updatedAnswerRatings->where('user_id', '=', $firstUserId)->map->rating
        );

        //assert there are no abnormalities for the user, because all its ratings are the same as the TEACHER,
        // even though the student rated everything different compared to the other students
        $this->assertEquals(
            expected: 0,
            actual: $firstUserAbnormalities
        );
    }


    /** @test */
    public function benchmarkTestTakeControllerMethodVsCoLearningHelper()
    {
        $testTake = $this->setUpTestTake();

        $user = User::find(1486);
        auth()->login($user);
        $testTakeId = $testTake->getKey();

        $benchmark = [];
        $result1 = null;
        $result2 = null;
        //end of set-up


        DB::enableQueryLog();

        $benchmark['TestTakesController']['time'] = Benchmark::measure(function () use (&$result1, $testTake) {
            return $result1 = $this->getTestParticipantsWithStatusOldController(
                testTake: $testTake,
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

    protected function setUpTestTake(): TestTake
    {
        $testTake = FactoryScenarioTestTakeDiscussed::createTestTake(
            user: $user = User::find(1486),
            test: $test = FactoryScenarioTestTestWithTwoQuestions::createTest('abnormalities-test', $user)
        );
        $testTake->update([
            'test_take_status_id' => TestTakeStatus::STATUS_DISCUSSING,
        ]);
        return $testTake;
    }

    protected function handleQueryLog(&$benchmark, $index)
    {
        $queryLog = collect(DB::getQueryLog());

        $benchmark[$index]['query-time'] = $queryLog->sum('time');
        $benchmark[$index]['queries'] = $queryLog->count();

        DB::flushQueryLog();
    }

    protected function getAnswerRatings(TestTake $testTake, $withTrashed = false): Collection
    {
        return AnswerRating::where('test_take_id', '=', $testTake->getKey())
            ->orderBy('answer_id')
            ->when($withTrashed, function ($query) {
                $query->withTrashed();
            })
            ->get();
    }

    protected function getTestParticipantsWithStatusOldController($testTake)
    {

        $request = new \Illuminate\Http\Request([
            'with' => ['participantStatus', 'discussingQuestion'],
        ]);

        return (new TestTakesController)->showFromWithin($testTake, $request, false);
    }
}
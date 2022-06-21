<?php

namespace Tests\Unit\FactoryTests\TestTakeFactory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use tcCore\AnswerRating;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeTakenOneQuestion;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeTakenTwoQuestions;
use Tests\TestCase;

class FactoryTestTakeStatusTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /** @test */
    public function can_set_TestTake_from_taken_to_discussing()
    {
        $FactoryTestTake = FactoryScenarioTestTakeTakenOneQuestion::create()->testTakeFactory;

        $FactoryTestTake->setStatusDiscussing();

        $this->assertEquals(7, $FactoryTestTake->testTake->test_take_status_id);
    }


    /** @test */
    public function can_set_TestTake_from_discussing_to_discussed()
    {
        $startCountAnswerRatings = AnswerRating::count();
        $FactoryTestTake = FactoryScenarioTestTakeTakenTwoQuestions::create()->testTakeFactory;

        $FactoryTestTake->setStatusDiscussing()
            ->addStudentAnswerRatings()
            ->setStatusDiscussed();

        $this->assertGreaterThan($startCountAnswerRatings, AnswerRating::count());
        $this->assertEquals(8, $FactoryTestTake->testTake->test_take_status_id);
    }

    /** @test */
    public function can_set_normalized_scores_for_each_TestParticipant()
    {
        $FactoryTestTake = FactoryScenarioTestTakeTakenTwoQuestions::create()->testTakeFactory;

        $FactoryTestTake->setStatusDiscussing()
            ->addStudentAnswerRatings()
            ->setStatusDiscussed()
            ->addTeacherAnswerRatings()
            ->setNormalizedScores();

        $FactoryTestTake->testTake->testParticipants()->each(function ($participant) {
            $this->assertTrue(1 <= $participant->rating && $participant->rating <= 10);
        });

        //todo assert normalized scores. testParticipant->rating
    }

    /** @test */
    public function can_set_TestTake_from_discussed_to_rated()
    {
        $FactoryTestTake = FactoryScenarioTestTakeTakenTwoQuestions::create()->testTakeFactory;

        $FactoryTestTake->setStatusDiscussing()
            ->addStudentAnswerRatings()
            ->setStatusDiscussed()
            ->addTeacherAnswerRatings()
            ->setNormalizedScores()
            ->setStatusRated();

        $this->assertEquals(9, $FactoryTestTake->testTake->test_take_status_id);
    }


}
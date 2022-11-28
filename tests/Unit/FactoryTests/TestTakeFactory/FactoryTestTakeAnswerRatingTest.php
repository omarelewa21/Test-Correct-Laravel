<?php

namespace Tests\Unit\FactoryTests\TestTakeFactory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use tcCore\AnswerRating;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeTakenOneQuestion;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeTakenTwoQuestions;
use Tests\TestCase;

class FactoryTestTakeAnswerRatingTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;


    /** @test */
    public function can_add_student_answer_ratings_to_discussing_testtake()
    {
        $startCountAnswerRatings = AnswerRating::count();
        $FactoryTestTake = FactoryScenarioTestTakeTakenOneQuestion::create()->testTakeFactory;

        $FactoryTestTake->setStatusDiscussing()
            ->addStudentAnswerRatings();

        //todo more specific assertions?
        $this->assertGreaterThan($startCountAnswerRatings, AnswerRating::count());
    }

    /** @test */
    public function can_add_teacher_answer_rating_to_discussed_testtake()
    {
        $startCountAnswerRatings = AnswerRating::count();
        $FactoryTestTake = FactoryScenarioTestTakeTakenTwoQuestions::create()->testTakeFactory;

        $FactoryTestTake->setStatusDiscussing()
            ->addStudentAnswerRatings()
            ->setStatusDiscussed();
        $this->assertGreaterThan($startCountAnswerRatings, AnswerRating::count());

        $startCountTeacherAnswerRatings = AnswerRating::count();
        $FactoryTestTake->addTeacherAnswerRatings();

        //equals +10, because: FactoryScenarioTestTakeTakenTwoQuestions has:
        // 2 questions and 5 participants (klas1) = 10 answers.
        // (if scenario has changed, test may fail)
        $this->assertEquals($startCountTeacherAnswerRatings + 10, AnswerRating::count());
        $this->assertEquals(8, $FactoryTestTake->testTake->test_take_status_id);
    }
}
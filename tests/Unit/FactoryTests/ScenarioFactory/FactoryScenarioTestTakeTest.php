<?php

namespace Tests\Unit\FactoryTests\ScenarioFactory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use tcCore\Answer;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeDiscussed;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeDiscussing;
use tcCore\FactoryScenarios\FactoryScenarioTestTakePlanned;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeRated;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeTaken;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeTakenOneQuestion;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeTakenTwoQuestions;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeTakingTest;
use tcCore\Question;
use tcCore\TestTake;
use Tests\TestCase;

/**
 * Create TestTake with all possible test_take_statusses by using Scenario Factories
 * Planned      = 1
 * Taking Test  = 3
 * Taken        = 6
 * Discussing   = 7
 * Discussed    = 8
 * Rated        = 9
 * (status 2, 4, 5 are not available for TestTakes)
 */
class FactoryScenarioTestTakeTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    const PLANNED_TEST_TAKE = 1;
    const TAKING_TEST_TEST_TAKE = 3;

    /** @test */
    public function can_create_scenario_with_test_and_testtake_for_specific_user()
    {
        $factoryScenarioSchoolTeacher = FactoryScenarioSchoolSimple::create()->getTeachers()->first();

        $factoryTestTakeScenario = FactoryScenarioTestTakePlanned::create($factoryScenarioSchoolTeacher);

        $this->assertEquals($factoryScenarioSchoolTeacher->getKey(), $factoryTestTakeScenario->test->author->getKey());
        $this->assertEquals($factoryScenarioSchoolTeacher->getKey(), $factoryTestTakeScenario->testTakeFactory->testTake->user->getKey());
    }

    /**
     * @test
     * Create TestTake with the status planned
     * This includes adding a valid school class as TestParticipants
     */
    public function can_create_scenario_with_TestTake_status_Planned()
    {
        $startCountTestTake = TestTake::count();

        $factoryTestTakeScenario = FactoryScenarioTestTakePlanned::create();

        $this->assertGreaterThan($startCountTestTake,
            TestTake::count()
        );
        $this->assertEquals(self::PLANNED_TEST_TAKE,
            $factoryTestTakeScenario->testTakeFactory->testTake->test_take_status_id
        );
        $this->assertGreaterThan(0,
            $factoryTestTakeScenario->testTakeFactory->testTake->testParticipants->count()
        );
    }

    /** @test */
    public function can_create_scenario_with_TestTake_status_Taking_Test()
    {
        $startCountTestTake = TestTake::count();

        $factoryTestTakeScenario = FactoryScenarioTestTakeTakingTest::create();

        $this->assertEquals(self::TAKING_TEST_TEST_TAKE,
            $factoryTestTakeScenario->testTakeFactory->testTake->test_take_status_id
        );
        $this->assertGreaterThan(0,
            $factoryTestTakeScenario->testTakeFactory->testTake->testParticipants->count()
        );
    }
    /**
     * @test
     * Create TestTake with Status Taken
     * Setting Test Take Status to 'Taken' includes adding answers to the TestTake.
     */
    public function can_create_scenario_with_TestTake_status_Taken()
    {
        $startCounts = [
            'testTake' => TestTake::count(),
            'question' => Question::count(),
            'answer' => Answer::count(),
        ];

        $factoryTestTakeScenario = FactoryScenarioTestTakeTaken::create();

        $this->assertGreaterThan($startCounts['testTake'], TestTake::count());
        $this->assertGreaterThan($startCounts['question'], Question::count());
        $this->assertGreaterThan($startCounts['answer'], Answer::count());
        $this->assertEquals(6, $factoryTestTakeScenario->testTakeFactory->testTake->test_take_status_id);
    }

    /**
     * @test
     * Create TestTake with status Taken,
     *      with less question-types added than the default
     */
    public function can_create_scenario_with_TestTake_status_Taken_Variations()
    {
        $startCount = TestTake::count();

        FactoryScenarioTestTakeTakenOneQuestion::create();
        FactoryScenarioTestTakeTakenOneQuestion::createTestTake();
        FactoryScenarioTestTakeTakenTwoQuestions::create();
        FactoryScenarioTestTakeTakenTwoQuestions::createTestTake();

        $this->assertEquals($startCount + 4, TestTake::count());
    }

    /** @test */
    public function can_create_scenario_with_TestTake_status_Taken_one_question()
    {
        $startCounts = [
            'testTake' => TestTake::count(),
            'question' => Question::count(),
            'answer' => Answer::count(),
        ];

        $factoryTestTakeScenario = FactoryScenarioTestTakeTakenOneQuestion::create();

        $this->assertGreaterThan($startCounts['testTake'], TestTake::count());
        $this->assertGreaterThan($startCounts['question'], Question::count());
        $this->assertGreaterThan($startCounts['answer'], Answer::count());
        $this->assertEquals(6, $factoryTestTakeScenario->testTakeFactory->testTake->test_take_status_id);
    }

    /** @test */
    public function can_create_scenario_with_TestTake_status_Discussing()
    {
        $factoryTestTakeScenario = FactoryScenarioTestTakeDiscussing::create();

        $this->assertEquals(7, $factoryTestTakeScenario->testTakeFactory->testTake->test_take_status_id);
    }
    /** @test */
    public function can_create_scenario_with_TestTake_status_Discussed()
    {
        $factoryTestTakeScenario = FactoryScenarioTestTakeDiscussed::create();

        $this->assertEquals(8, $factoryTestTakeScenario->testTakeFactory->testTake->test_take_status_id);
    }
    /** @test */
    public function can_create_scenario_with_TestTake_status_Rated()
    {
        $factoryTestTakeScenario = FactoryScenarioTestTakeRated::create();

        $this->assertEquals(9, $factoryTestTakeScenario->testTakeFactory->testTake->test_take_status_id);
    }

}
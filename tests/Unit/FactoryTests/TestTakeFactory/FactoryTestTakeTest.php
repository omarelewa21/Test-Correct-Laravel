<?php

namespace Tests\Unit\FactoryTests\TestTakeFactory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use tcCore\Factories\FactoryTestParticipant;
use tcCore\Factories\FactoryTestTake;
use tcCore\FactoryScenarios\FactoryScenarioTestTestWithOpenShortQuestion;
use tcCore\Test;
use tcCore\TestParticipant;
use tcCore\TestQuestion;
use tcCore\TestTake;
use Tests\TestCase;

class FactoryTestTakeTest extends TestCase
{
    const STATUS_PLANNED = 1;
    const STATUS_TEST_NOT_TAKEN = 2;
    const STATUS_TAKING_TEST = 3;
    const STATUS_HANDED_IN = 4;
    const STATUS_TAKEN_AWAY = 5;
    const STATUS_TAKEN = 6;
    const STATUS_DISCUSSING = 7;
    const STATUS_DISCUSSED = 8;
    const STATUS_RATED = 9;

    use DatabaseTransactions;
    use WithFaker;

    // 1)   TestParticipant test_take_status_id, at creating testtake (status planned) and adding participants:
    //      testTake->test_take_status_id => 1 (planned)
    //          testParticipant->test_take_status_id = 1 (planned)
    // 2)   setting testTake->test_take_status_id => 3 (Taking test)
    //          testParticipant->test_take_status_id = 2 (not taken)
    // 3)       when participant presses join/start test: if test take === taking test, change
    //          testParticipant->test_take_status_id = 3 (taking test)
    //          testParticipant has a saved boot method!
    //              answers gets filled with each answer a testParticipant fills in (even infoscreen)
    //


    //  Participants need to be updated between setting
    //      testParticipant->test_take_status_id = 3 (taking test)
    //  and
    //      testParticipant->test_take_status_id = 6 (taken)

    //  first change status of participant to (3) taking test

    // second add Answer records?

    // third collecting the test (individual), changes just the status?
    // collecting the test for the whole class, changes both status of test and answers?





    /** @test */
    public function can_create_a_test_take_with_a_new_test()
    {
        $startCountTest = Test::count();
        $startCountTestQuestion = TestQuestion::count();
        $startCountTestTake = TestTake::count();

        $test = FactoryScenarioTestTestWithOpenShortQuestion::createTest();

        FactoryTestTake::create($test);

        $this->assertGreaterThan($startCountTest, Test::count());
        $this->assertGreaterThan($startCountTestQuestion, TestQuestion::count());
        $this->assertGreaterThan($startCountTestTake, TestTake::count());
    }

    /** @test */
    public function can_create_testTake_without_participants()
    {
        $startCountTestTake = TestTake::count();
        $test = FactoryScenarioTestTestWithOpenShortQuestion::createTest();

        $factoryTestTake = FactoryTestTake::create($test);

        $this->assertEquals($startCountTestTake + 1, TestTake::count());
    }

    /** @test */
    public function can_create_testTake_with_participants()
    {
        $startCountTestTake = TestTake::count();
        $startCountTestParticipants = TestParticipant::count();
        $test = FactoryScenarioTestTestWithOpenShortQuestion::createTest();

        $factoryTestTake = FactoryTestTake::createWithParticipants($test);

        $this->assertGreaterThan($startCountTestTake, TestTake::count());
        $this->assertGreaterThan($startCountTestParticipants, TestParticipant::count());
    }

    /** @test */
    public function can_create_a_testTake_with_custom_properties()
    {
        $startCountTestTake = TestTake::count();
        $test = FactoryScenarioTestTestWithOpenShortQuestion::createTest();
        $testTakeProperties = [
            'weight'                  => '10',
            'guest_accounts'          => '1',
            'allow_inbrowser_testing' => '1',
            'test_take_status_id'     => 1,
        ];

        $factoryTestTake = FactoryTestTake::create($test)
            ->setProperties($testTakeProperties);

        $this->assertGreaterThan($startCountTestTake, TestTake::count());
        foreach ($testTakeProperties as $key => $value) {
            $this->assertTrue($factoryTestTake->testTake->getAttributes()[$key] == $value);
        }
    }

    /** @test */
    public function can_create_a_testTake_with_status_planned()
    {
        $startCountTestTake = TestTake::count();
        $test = FactoryScenarioTestTestWithOpenShortQuestion::createTest();

        $factoryTestTake = FactoryTestTake::create($test)
            ->setStatusPlanned();

        $this->assertGreaterThan($startCountTestTake, TestTake::count());
        $this->assertEquals(self::STATUS_PLANNED, $factoryTestTake->testTake->getAttribute('test_take_status_id'));
    }

    /** @test */
    public function can_create_a_test_with_status_taking_test()
    {
        $test = FactoryScenarioTestTestWithOpenShortQuestion::createTest();

        $factoryTestTake = FactoryTestTake::create($test)
            ->addParticipants()
            ->setStatusTakingTest();

        $this->assertEquals(3, $factoryTestTake->testTake->test_take_status_id);
    }
    
    /** @test */
    public function can_create_testTake_with_status_Taken()
    {
        $test = FactoryScenarioTestTestWithOpenShortQuestion::createTest();

        $factoryTestTake = FactoryTestTake::create($test)
            ->addFirstSchoolClassAsParticipants()
            ->setStatusTakingTest()
            ->setStatusTaken(); //todo set taken without answers is incorrect, but accepted

        $this->assertEquals(self::STATUS_TAKEN, $factoryTestTake->testTake->testTakeStatus->getKey());
    }

    /** @test */
    public function can_create_testTake_and_add_participants_from_klas1_and_demoKlas()
    {
        $startCountTestTake = TestTake::count();
        $test = FactoryScenarioTestTestWithOpenShortQuestion::createTest();
        $startCountTestParticipant = TestParticipant::count();

        // class 1 and student 1621, 1623 are dependant on the database...
        $studentIds = [1621, 1623];
        $studentsClassId = 33;

        $schoolClassId = [1];

        $factoryTestTake = FactoryTestTake::create($test)
            ->addParticipants([
                FactoryTestParticipant::makeWithUserAndClass($studentIds, $studentsClassId),
                FactoryTestParticipant::makeForAllUsersInClass($schoolClassId),
            ]);

        $this->assertGreaterThan(0, $factoryTestTake->testTake->testParticipants()->count());
        $this->assertGreaterThan($startCountTestParticipant, TestParticipant::count());
        $this->assertGreaterThan($startCountTestTake, TestTake::count());
    }

    /** @test */
    public function can_update_or_set_invigilators_with_set_properties()
    {
        $test = FactoryScenarioTestTestWithOpenShortQuestion::createTest();

        $factoryTestTake = FactoryTestTake::create($test);
        $startCountInvigilators = $factoryTestTake->testTake->invigilators()->count();

        $factoryTestTake->setProperties(['invigilators' => [1486, 1496]]);

        $this->assertGreaterThan($startCountInvigilators, $factoryTestTake->testTake->invigilators()->count());
    }

    /** @test */
    public function created_test_take_has_invigilator_by_default()
    {
        $test = FactoryScenarioTestTestWithOpenShortQuestion::createTest();
        $factoryTestTake = FactoryTestTake::create($test);

        $this->assertGreaterThan(0, $factoryTestTake->testTake->invigilators->count());

    }

    /** @test */
    public function can_create_test_take_with_random_valid_school_class_as_participants()
    {
        $test = FactoryScenarioTestTestWithOpenShortQuestion::createTest();

        $factoryTestTake = FactoryTestTake::create($test);
        $startCountParticipants = $factoryTestTake->testTake->testParticipants->count();

        $factoryTestTake->addRandomParticipants();

        $this->assertGreaterThan($startCountParticipants, $factoryTestTake->testTake->testParticipants()->count());
    }

    /** @test */
    public function can_create_test_take_with_first_school_class_as_participants()
    {
        $test = FactoryScenarioTestTestWithOpenShortQuestion::createTest();

        $factoryTestTake = FactoryTestTake::create($test);
        $startCountParticipants = $factoryTestTake->testTake->testParticipants()->count();

        $factoryTestTake->addFirstSchoolClassAsParticipants();

        $this->assertGreaterThan($startCountParticipants, $factoryTestTake->testTake->testParticipants()->count());

    }

}
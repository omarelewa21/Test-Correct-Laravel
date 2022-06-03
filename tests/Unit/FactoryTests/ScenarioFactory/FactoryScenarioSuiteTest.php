<?php

namespace Tests\Unit\FactoryTests\ScenarioFactory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use tcCore\FactoryScenarios\FactoryScenarioSchool001;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeAllStatuses;
use tcCore\QuestionAttachment;
use tcCore\TestTake;
use Tests\TestCase;

class FactoryScenarioSuiteTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /**
     * @test asserts that:
     * -> 6 TestTakes have been made
     * -> the TestTake->Tests contain a question with attachments
     */
    public function can_create_scenario_with_TestTakes_for_all_statuses()
    {
        $testTakesAmountCreated = 6;
        $attachmentsPerTestTake = 4;
        $totalAttachmentsCreated = $testTakesAmountCreated * $attachmentsPerTestTake;

        $startCountTestTake = TestTake::count();
        $startCountQuestionAttachments = QuestionAttachment::count();

        $factoryTestTakeScenario = FactoryScenarioTestTakeAllStatuses::create();

        $this->assertEquals($startCountTestTake + $testTakesAmountCreated, TestTake::count());
        $this->assertEquals($startCountQuestionAttachments + $totalAttachmentsCreated, QuestionAttachment::count());
    }
    
    /** @test */
    public function can_create_school_scenario001_with_TestTake_for_all_testTake_statuses()
    {
        $factorySchoolScenario = FactoryScenarioSchool001::create();

        $teacherUser = $factorySchoolScenario->getTeachers()->first();

        $factoryTestTakeScenario = FactoryScenarioTestTakeAllStatuses::create($teacherUser);

        $statusIds = collect([]);
        $factoryTestTakeScenario->getScenarioFactories()->each(function ($testTakeScenarioFactory) use (&$statusIds) {
            $statusIds->add($testTakeScenarioFactory->testTakeFactory->testTake->test_take_status_id);
        });
        //contains 1,3,6,7,8,9
        $this->assertContains(1, $statusIds);
        $this->assertContains(3, $statusIds);
        $this->assertContains(6, $statusIds);
        $this->assertContains(7, $statusIds);
        $this->assertContains(8, $statusIds);
        $this->assertContains(9, $statusIds);
    }
}
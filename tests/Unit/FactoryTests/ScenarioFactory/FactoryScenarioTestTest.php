<?php

namespace Tests\Unit\FactoryTests\ScenarioFactory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use tcCore\Attachment;
use tcCore\FactoryScenarios\FactoryScenarioTestBiologie;
use tcCore\FactoryScenarios\FactoryScenarioTestScheikunde;
use tcCore\FactoryScenarios\FactoryScenarioTestTestWithAllQuestionTypes;
use tcCore\FactoryScenarios\FactoryScenarioTestTestWithOpenShortQuestion;
use tcCore\FactoryScenarios\FactoryScenarioTestTestWithTwoQuestions;
use tcCore\OpenQuestion;
use tcCore\QuestionAttachment;
use tcCore\Test;
use tcCore\TestQuestion;
use tcCore\User;
use Tests\TestCase;

class FactoryScenarioTestTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    /**
     * The school/school location are referenced from the passed user
     * @test
     */
    public function can_create_scenario_for_specific_user()
    {
        $teacherUser = User::find(1500);

        $scenarioFactory = FactoryScenarioTestTestWithAllQuestionTypes::create('name', $teacherUser);

        $this->assertEquals(1500,$scenarioFactory->getTestModel()->author->getKey());
    }

    /** @test */
    public function can_create_scenario_one_of_all_questions()
    {
        $startCountTest = Test::count();
        $startCountTestQuestion = TestQuestion::count();

        $scenarioFactory = FactoryScenarioTestTestWithAllQuestionTypes::create();

        $this->assertGreaterThan($startCountTest, Test::count());
        $this->assertGreaterThan($startCountTestQuestion, TestQuestion::count());
    }

    /** @test */
    public function scenario_with_all_questions_contains_attachments()
    {
        $startCountQuestionAttachments = QuestionAttachment::count();
        $startCountAttachments = Attachment::count();

        $scenarioFactory = FactoryScenarioTestTestWithAllQuestionTypes::create();

        $this->assertEquals($startCountQuestionAttachments + 4, QuestionAttachment::count());
        $this->assertEquals($startCountAttachments + 4, Attachment::count());
    }

    /** @test */
    public function can_create_scenario_one_open_question()
    {
        $startCountTest = Test::count();
        $startCountTestOpenShort = OpenQuestion::count();

        FactoryScenarioTestTestWithOpenShortQuestion::create();

        $this->assertEquals($startCountTest + 1, Test::count());
        $this->assertEquals($startCountTestOpenShort + 1, OpenQuestion::count());
    }

    /** @test */
    public function can_create_scenario_with_two_questions()
    {
        $startCountTest = Test::count();
        $startCountTestQuestions = TestQuestion::count();

        FactoryScenarioTestTestWithTwoQuestions::create();

        $this->assertEquals($startCountTest + 1, Test::count());
        $this->assertEquals($startCountTestQuestions + 2, TestQuestion::count());
    }

    /** @test */
    public function can_create_test_scenario_and_return_test_model()
    {
        $startCountTest = Test::count();

        $testModel = FactoryScenarioTestTestWithAllQuestionTypes::createTest();

        $this->assertInstanceOf('tcCore\Test', $testModel);
        $this->assertEquals($startCountTest + 1, Test::count());
    }
    /** @test */
    public function can_create_test_scenario_with_custom_test_name()
    {
        $startCountTest = Test::count();

        $testModel = FactoryScenarioTestTestWithAllQuestionTypes::createTest('Test for bug #1');
        $testScenarioFactory = FactoryScenarioTestTestWithAllQuestionTypes::create('Test for bug #2');

        $this->assertEquals('Test for bug #1', $testModel->name);
        $this->assertEquals('Test for bug #2', $testScenarioFactory->getTestModel()->name);
        $this->assertEquals($startCountTest + 2, Test::count());
    }
    /** @test */
    public function can_create_test_scenario_with_a_specific_subject_biologie()
    {
        $startCountTest = Test::count();

        $testModel = FactoryScenarioTestBiologie::createTest();

        $this->assertEquals($startCountTest + 1, Test::count());
        $this->assertEquals(1, $testModel->subject->section_id);
        $this->assertEquals("Biologie", $testModel->subject->name);
    }
    /** @test */
    public function can_create_test_scenario_with_a_specific_subject_scheikunde()
    {
        $startCountTest = Test::count();

        $testModel = FactoryScenarioTestScheikunde::createTest();

        $this->assertEquals($startCountTest + 1, Test::count());
        $this->assertEquals(1, $testModel->subject->section_id);
        $this->assertEquals("Scheikunde", $testModel->subject->name);
    }
}
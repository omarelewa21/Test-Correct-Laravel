<?php

namespace Tests\Unit\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use tcCore\Answer;
use tcCore\Factories\FactoryTestParticipant;
use tcCore\Factories\FactoryTestTake;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\FactoryScenarios\FactoryScenarioTestTestWithOpenShortQuestion;
use tcCore\FactoryScenarios\FactoryScenarioTestTestWithTwoQuestions;
use tcCore\Http\Controllers\SurveillanceController;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Question;
use tcCore\SchoolClass;
use tcCore\SchoolLocationIp;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\TestTakeStatus;
use tcCore\User;
use Tests\ScenarioLoader;
use Tests\TestCase;
use Tests\Traits\TestTakeTrait;

class SurveillanceControllerTest extends TestCase
{
    use DatabaseTransactions;

    use TestTakeTrait;

    protected $loadScenario = FactoryScenarioSchoolSimple::class;

    private User $teacher;

    protected function setUp(): void
    {
        parent::setUp();
        $this->teacher = ScenarioLoader::get('user');
    }

    /** @test */
    public function the_surveilance_controller_response_holds_records_for_every_test_participant()
    {
        $this->actingAs($this->teacher);
        $test = FactoryScenarioTestTestWithOpenShortQuestion::createTest(user: $this->teacher);
        $factoryTestTake = FactoryTestTake::create($test)
            ->addFirstSchoolClassAsParticipants()
            ->setStatusTakingTest();

        $response = (new SurveillanceController)->index(new Request);

        collect(['takes', 'participants'])->each(fn($expected) => $this->assertArrayHasKey($expected, $response));

        $factoryTestTake->testTake->testParticipants->each(function ($participant) use ($response) {
            collect([
                'percentage',
                'label',
                'text',
                'alert',
                'ip',
                'status',
                'allow_inbrowser_testing',
            ])->each(fn($key) => $this->assertArrayHasKey($key, $response['participants'][$participant->uuid]));
        });
    }

    /** @test */
    public function when_student_one_enters_a_test_his_test_take_status_should_go_to_three()
    {
        $this->actingAs($this->teacher);
        $test = FactoryScenarioTestTestWithOpenShortQuestion::createTest(user: $this->teacher);
        $factoryTestTake = FactoryTestTake::create($test)
            ->addFirstSchoolClassAsParticipants()
            ->setStatusTakingTest();

        $response = (new SurveillanceController)->index(new Request);
        $factoryTestTake->testTake->testParticipants->each(function ($participant) use ($response) {
            $this->assertEquals(TestTakeStatus::STATUS_TEST_NOT_TAKEN, $response['participants'][$participant->uuid]['status']);
        });

//        $this->initTestTakeForStudent($factoryTestTake->testTake->uuid, $factoryTestTake->testTake->testParticipants->first()->uuid);

        $factoryTestTake->setTestParticipantTakingTest($factoryTestTake->testTake->testParticipants->first());
        $newResponse = (new SurveillanceController)->index(new Request);

        $factoryTestTake->testTake->testParticipants->each(function ($participant, $key) use ($newResponse) {
            $expected = $key == 0 ? TestTakeStatus::STATUS_TAKING_TEST : TestTakeStatus::STATUS_TEST_NOT_TAKEN;
            $this->assertEquals($expected, $newResponse['participants'][$participant->uuid]['status']);
        });
    }

    /** @test */
    public function when_a_student_adds_answers_to_a_take_the_progress_indicator_for_the_take_and_participant_changes()
    {
        $this->actingAs($this->teacher);
        $test = FactoryScenarioTestTestWithOpenShortQuestion::createTest(user: $this->teacher);
        $factoryTestTake = FactoryTestTake::create($test)
            ->addFirstSchoolClassAsParticipants()
            ->setStatusTakingTest()
            ->setTestParticipantsTakingTest();

        $response = (new SurveillanceController)->index(new Request);
        // the first array of takes has 0 progress;
        $this->assertEquals(0, array_shift($response['takes']));

        // add an answer for participantOne;
        $firstParticipant = $factoryTestTake->testTake->testParticipants->first();
        $factoryTestTake->fillAnswer($firstParticipant->answers()->first());

        // this test has 1 questions so progress should be 33;
        $responseWithOneAnswerAdded = (new SurveillanceController)->index(new Request);

        $this->assertEquals(33, array_shift($responseWithOneAnswerAdded['takes']));
        // progress for participant one is 100;
        $this->assertEquals(100, $responseWithOneAnswerAdded['participants'][$firstParticipant->uuid]['percentage']);
    }

    /** @test */
    public function when_i_double_the_score_of_a_answered_question_it_should_reflect_in_percentages_for_take_and_participant()
    {
        $this->actingAs($this->teacher);
        $test = FactoryScenarioTestTestWithTwoQuestions::createTest(user: $this->teacher);
        $factoryTestTake = FactoryTestTake::create($test)
            ->addFirstSchoolClassAsParticipants()
            ->setStatusTakingTest()
            ->setTestParticipantsTakingTest();

        $firstParticipant = $factoryTestTake->testTake->testParticipants->first();
        $factoryTestTake->fillAnswer($firstParticipant->answers()->first());

        $response = (new SurveillanceController)->index(new Request);
// progress is 50% for student one
        $this->assertEquals(50, array_shift($response['participants'][$firstParticipant->uuid]));
// now double the score for the answered question;
        $question = $firstParticipant->answers()->first()->question->getQuestionInstance();
        $question->score = $question->score * 2;
        $question->save();

        $newResponse = (new SurveillanceController)->index(new Request);
// progress s now 67% for student one
        $this->assertEquals(67, array_shift($newResponse['participants'][$firstParticipant->uuid]));
    }

    /** @test */
    public function surveillence_data_should_reflect_toggleInbrowserTestingForAllParticipants()
    {
        $this->actingAs($this->teacher);
        $test = FactoryScenarioTestTestWithTwoQuestions::createTest(user: $this->teacher);
        $factoryTestTake = FactoryTestTake::create($test)
            ->addFirstSchoolClassAsParticipants()
            ->setStatusTakingTest()
            ->setTestParticipantsTakingTest();

        $response = (new SurveillanceController)->index(new Request);
        collect($response['participants'])->each(function ($participant) {
            $this->assertTrue($participant['allow_inbrowser_testing']);
        });

        $factoryTestTake->testTake->allow_inbrowser_testing = false;
        $factoryTestTake->testTake->save();

        $response = (new SurveillanceController)->index(new Request);
        collect($response['participants'])->each(function ($participant) {
            $this->assertFalse($participant['allow_inbrowser_testing']);
        });
    }

    /** @test */
    public function surveillence_data_should_reflect_toggleInbrowserTestingForSpecificParticipant()
    {
        $this->actingAs($this->teacher);
        $test = FactoryScenarioTestTestWithTwoQuestions::createTest(user: $this->teacher);
        $factoryTestTake = FactoryTestTake::create($test)
            ->addFirstSchoolClassAsParticipants()
            ->setStatusTakingTest()
            ->setTestParticipantsTakingTest();

        $response = (new SurveillanceController)->index(new Request);
        collect($response['participants'])->each(function ($participant) {
            $this->assertTrue($participant['allow_inbrowser_testing']);
        });

        $testParicipantOne = $factoryTestTake->testTake->testParticipants->first();
        $testParicipantOne->allow_inbrowser_testing = false;
        $testParicipantOne->save();

        $response = (new SurveillanceController)->index(new Request);
        collect($response['participants'])->each(function ($participant, $participantUuid) use ($testParicipantOne) {
            if ($participantUuid == $testParicipantOne->uuid) {
                $this->assertFalse($participant['allow_inbrowser_testing']);
            } else {
                $this->assertTrue($participant['allow_inbrowser_testing']);
            }
        });
    }

    /** @test */
    public function when_i_start_the_test_suit_no_test_take_is_assigned_to_teacher_one()
    {
        // when this test failes you should php artisan test:refreshdb
        Auth::login($this->teacher);

        $response = (new SurveillanceController)->index(new Request);

        $this->assertEquals([], $response['takes']);
    }

    /** @test */
    public function it_should_reflect_the_correct_alertStatus()
    {
        Auth::login($this->teacher);

        $response = (new SurveillanceController)->index(new Request);

        $this->assertEquals(
            0,
            $response['alerts']
        );
    }

    /** @test */
    public function time_key_should_reflect_current_time_in_24_hours_and_minutes()
    {
        $this->actingAs($this->teacher);
        Carbon::setTestNow(Carbon::create(now()->year, now()->month, now()->day, 12, 10));

        $response = (new SurveillanceController)->index(new Request);
        $this->assertEquals('12:10', $response['time']);
    }

    /** @test */
    public function it_should_contain_the_correct_keys()
    {
        $this->actingAs($this->teacher);
        $response = ((new SurveillanceController)->index(new Request));

        collect(['takes', 'participants', 'time', 'alerts', 'ipAlerts'])->each(function ($key) use ($response) {
            $this->assertArrayHasKey($key, $response);
        });
    }
}

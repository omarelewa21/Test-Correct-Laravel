<?php

namespace Tests\Unit\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use tcCore\Answer;
use tcCore\Http\Controllers\SurveillanceController;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Question;
use tcCore\SchoolClass;
use tcCore\SchoolLocationIp;
use tcCore\TestParticipant;
use tcCore\TestTake;
use Tests\TestCase;
use Tests\Traits\TestTakeTrait;

class SurveillanceControllerTest extends TestCase
{
    use DatabaseTransactions;

    use TestTakeTrait;

    /** @test */
    public function when_student_one_enters_a_test_his_test_take_status_should_go_to_three()
    {
        $testTakeUuid = TestTake::find($take_id = $this->startTestTakeFor(null, null))->uuid;
        Auth::login(self::getTeacherOne());

//        dd($response = ((new SurveillanceController)->index()));

        $testParicipantUuid = TestParticipant::where([
            ['test_take_id', $take_id],
            ['user_id', self::getStudentOne()->getKey()],
        ])->first()
            ->uuid;

        $testParicipantTwoUuid = TestParticipant::where([
            ['test_take_id', $take_id],
            ['user_id', self::getStudentOne()->getKey()],
        ])->first()
            ->uuid;

        $response = (new SurveillanceController)->index();
        $this->assertEquals(2, $response['participants'][$testParicipantUuid]['status']);
        $this->assertEquals(2, $response['participants'][$testParicipantTwoUuid]['status']);

        $this->initTestTakeForStudent($testTakeUuid, $testParicipantUuid);
        $newResponse = (new SurveillanceController)->index();
        $this->assertEquals(
            [
                'percentage'              => 0,
                "label"                   => "success",
                "text"                    => "Maakt toets",
                "alert"                   => false,
                "ip"                      => true,
                "status"                  => 3,
                "allow_inbrowser_testing" => false,
            ],
            $newResponse['participants'][$testParicipantUuid]
        );
        $this->assertEquals(2, $response['participants'][$testParicipantTwoUuid]['status']);
    }

    /** @test */
    public function when_a_student_adds_answers_to_a_take_the_progress_indicator_for_the_take_and_participant_changes()
    {
        $testTakeUuid = TestTake::find(
            $take_id = $this->startTestTakeFor(null, null)
        )->uuid;

        $testParicipant = TestParticipant::where([
            ['test_take_id', $take_id],
            ['user_id', self::getStudentOne()->getKey()],
        ])->first();

        $testParicipantTwo = TestParticipant::where([
            ['test_take_id', $take_id],
            ['user_id', self::getStudentTwo()->getKey()],
        ])->first();

        $this->initTestTakeForStudent($testTakeUuid, $testParicipant->uuid);
        $responseWithoutAnswersAdded = (new SurveillanceController)->index();

        // the first array of takes has 0 progress;
        $this->assertEquals(0, array_shift($responseWithoutAnswersAdded['takes']));

        // add an answer for participantOne;
        $this->fillAnswersForParticipant($testParicipant, 1);
        // this test has 3 questions so progress should be 33;
        $responseWithOneAnswerAdded = (new SurveillanceController)->index();
        $this->assertEquals(33, array_shift($responseWithOneAnswerAdded['takes']));
        // progress for participant one is 33;
        $this->assertEquals(
            33,
            $responseWithOneAnswerAdded['participants'][$testParicipant->uuid]['percentage']
        );
        $this->assertEquals(
            0,
            $responseWithOneAnswerAdded['participants'][$testParicipantTwo->uuid]['percentage']
        );
        $this->assertEquals(
            2,
            $responseWithOneAnswerAdded['participants'][$testParicipantTwo->uuid]['status']
        );
        // if I add one active participant the progress should go to 17
        $this->initTestTakeForStudent($testTakeUuid, $testParicipantTwo->uuid);
        $responseWithExtraParticipantAdded = (new SurveillanceController)->index();
        $this->assertEquals(
            3,
            $responseWithExtraParticipantAdded['participants'][$testParicipantTwo->uuid]['status']
        );
        $this->assertEquals(
            17,
            array_shift($responseWithExtraParticipantAdded['takes'])
        );
        $this->assertEquals(
            33,
            $responseWithExtraParticipantAdded['participants'][$testParicipant->uuid]['percentage']
        );

        // when all answers are filled the progress should be 100;
        $this->fillAnswersForParticipant($testParicipant, 3);
        $this->fillAnswersForParticipant($testParicipantTwo, 3);

        $responseWithAllAnswersFilled = (new SurveillanceController)->index();
        $this->assertEquals(
            100,
            array_shift($responseWithAllAnswersFilled['takes'])
        );
    }

    /** @test */
    public function when_i_double_the_score_of_a_answered_question_it_should_reflect_in_percentages_for_take_and_participant(
    )
    {
        $testTake = TestTake::find(
            $take_id = $this->startTestTakeFor(null, null)
        );

        $testParicipant = TestParticipant::where([
            ['test_take_id', $take_id],
            ['user_id', self::getStudentOne()->getKey()],
        ])->first();
        $this->initTestTakeForStudent($testTake->uuid, $testParicipant->uuid);
        // add an answer for participantOne;
        $this->fillAnswersForParticipant($testParicipant, 1);

        $responseWithOneAnswerAdded = (new SurveillanceController)->index();
        $this->assertEquals(33, array_shift($responseWithOneAnswerAdded['takes']));
        // progress for participant one is 33;
        $this->assertEquals(
            33,
            $responseWithOneAnswerAdded['participants'][$testParicipant->uuid]['percentage']
        );

        $question = ($testParicipant->answers()->where('done', '1')->first()->question->getQuestionInstance());
        $question->score = $question->score * 2;
        $question->save();


        $responseAfterDoublingScore = (new SurveillanceController)->index();
        $this->assertEquals(50, array_shift($responseAfterDoublingScore['takes']));
        // progress for participant one is 33;
        $this->assertEquals(
            50,
            $responseAfterDoublingScore['participants'][$testParicipant->uuid]['percentage']
        );
    }

    /** @test */
    public function surveillence_data_should_reflect_toggleInbrowserTestingForAllParticipants()
    {
        $testTake = TestTake::find(
            $take_id = $this->startTestTakeFor(null, null)
        );

        $response = (new SurveillanceController)->index();
        collect($response['participants'])->each(function ($participant) {
            $this->assertFalse($participant['allow_inbrowser_testing']);
        });

        $this->toggleInBrowserTestingForTestTake($testTake);

        $response = (new SurveillanceController)->index();
        collect($response['participants'])->each(function ($participant) {
            $this->assertTrue($participant['allow_inbrowser_testing']);
        });

        $this->toggleInBrowserTestingForTestTake($testTake);

        $response = (new SurveillanceController)->index();
        collect($response['participants'])->each(function ($participant) {
            $this->assertFalse($participant['allow_inbrowser_testing']);
        });
    }

    /** @test */
    public function surveillence_data_should_reflect_toggleInbrowserTestingForSpecificParticipant()
    {
        // http://testportal.test-correct.test/test_takes/toggle_inbrowser_testing_for_participant/a02c5710-0045-484f-9f8c-cfc0cdef9422/e4d3a8b4-9ee0-4ea3-800a-bba0aae68f53
        $testTake = TestTake::find(
            $take_id = $this->startTestTakeFor(null, null)
        );

        $testParicipantOne = TestParticipant::where([
            ['test_take_id', $take_id],
            ['user_id', self::getStudentOne()->getKey()],
        ])->first();

        $response = (new SurveillanceController)->index();
        collect($response['participants'])->each(function ($participant) {
            $this->assertFalse($participant['allow_inbrowser_testing']);
        });

        $this->toggleInBrowserTestingForTestTakeAndParticipant($testTake, $testParicipantOne);
        $response = (new SurveillanceController)->index();
        collect($response['participants'])->each(function ($participant, $participantUuid) use ($testParicipantOne) {
            if ($participantUuid == $testParicipantOne->uuid) {
                $this->assertTrue($participant['allow_inbrowser_testing']);
            } else {
                $this->assertFalse($participant['allow_inbrowser_testing']);
            }
        });
        /**  @TODO when i toggle again it all users should be off agian */

        $response = (new SurveillanceController)->index();
    }

    /** @test */
    public function when_i_start_the_test_suit_no_test_take_is_assigned_to_teacher_one()
    {
        // when this test failes you should php artisan test:refreshdb
        Auth::login(self::getTeacherOne());

        $response = ((new SurveillanceController)->index());

        $this->assertEquals([], $response['takes']);
    }

    /** @test */
    public function when_one_test_take_is_started_takes_should_contain_one_take_and_five_participants()
    {
        $this->startTestTakeFor(null, null);

        Auth::login(self::getTeacherOne());

        $response = ((new SurveillanceController)->index());

        $this->assertCount(1, $response['takes']);
        $this->assertCount(5, $response['participants']);
    }

    /** @test */
    public function it_should_reflect_the_correct_alertStatus()
    {
        Auth::login(self::getTeacherOne());

        $response = ((new SurveillanceController)->index());

        $this->assertEquals(
            0,
            $response['alerts']
        );

//        dd($response);
    }


    /** @test */
    public function takes_key_should_contain_progress()
    {
        $this->startTestTakeFor(null, null);
        Auth::login(self::getTeacherOne());

        $response = ((new SurveillanceController)->index());
        $firstKey = array_key_first($response['takes']);

        $this->assertStringContainsString('progress_', $firstKey);

        $progress = array_pop($response['takes']);
        $this->assertEquals(0, $progress);
    }

    /** @test */
    public function takes_key_should_contain_three_test_takes()
    {
        $this->startTestTakeFor(null, null);
        $this->startTestTakeFor(null, null);
        $this->startTestTakeFor(null, null);
        Auth::login(self::getTeacherOne());

        $response = ((new SurveillanceController)->index());

        $this->assertCount(3, $response['takes']);
    }

    /** @test */
    public function time_key_should_reflect_current_time_in_24_hours_and_minutes()
    {
        Auth::login(self::getTeacherOne());
        Carbon::setTestNow(Carbon::create(now()->year, now()->month, now()->day, 12, 10));

        $response = ((new SurveillanceController)->index());
        $this->assertEquals('12:10', $response['time']);
    }

    /** @test */
    public function it_should_contain_the_correct_keys()
    {
        Auth::login(self::getTeacherOne());
        $response = ((new SurveillanceController)->index());

        collect(['takes', 'participants', 'time', 'alerts', 'ipAlerts'])->each(function ($key) use ($response) {
            $this->assertArrayHasKey($key, $response);
        });
    }

//    /** @test */
//    public function it_should_report_a_ip_warning_for_a_parcipant()
//    {
//        tap(self::getTeacherOne()->schoolLocation, function ($schoolLocation) {
//            $this->assertEmpty($schoolLocation->schoolLocationIps);
//        })->attach(
//            tap(new SchoolLocationIp(), function ($schoolLocationIp) {
//                $schoolLocationIp->ip = '10.10.0.3';
////                $schoolLocationIp->netmask = '255.255.255.0';
//                $schoolLocationIp->save();
//            })
//        );
//
//
//        $testTakeUuid = TestTake::find($take_id = $this->startTestTakeFor(null, null))->uuid;
//        Auth::login(self::getTeacherOne());
////        dd($response = ((new SurveillanceController)->index()));
//
//        $testParicipantUuid = TestParticipant::where([
//            ['test_take_id', $take_id],
//            ['user_id', self::getStudentOne()->getKey()],
//        ])->first()
//            ->uuid;
//
//
//        $this->initTestTakeForStudent($testTakeUuid, $testParicipantUuid);
//        $newResponse = (new SurveillanceController)->index();
//        $this->assertEquals(
//            [
//                'percentage'              => 0,
//                "label"                   => "success",
//                "text"                    => "Maakt toets",
//                "alert"                   => false,
//                "ip"                      => true,
//                "status"                  => 3,
//                "allow_inbrowser_testing" => false,
//            ],
//            $newResponse['participants'][$testParicipantUuid]
//        );
//
//
//    }
}

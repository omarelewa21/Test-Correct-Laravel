<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HeartBeatTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function it_should_return_a_heart_beat_when_a_user_is_registered_for_a_test()
    {
        $newTestTakeData = [
            'date'                => Carbon::now()->format('d-m-Y'),
            'period_id'           => 1,
            'invigilators'        => [1486],
            'class_id'            => 1,
            'test_id'             => 1,
            'weight'              => 1,
            'invigilator_note'    => '',
            'time_start'          => Carbon::now()->format('Y-m-d H:i:s'),
            'retake'              => 0,
            'test_take_status_id' => 1,
            "school_classes"      => ["1"],
        ];

        $scheduledResponse = $this->post(
            'test_take',
            static::getTeacherOneAuthRequestData($newTestTakeData)
        );
        $scheduledResponse->assertStatus(200);

        $this->toetsActiveren($scheduledResponse->decodeResponseJson()['id']);


        $testTakeId = $scheduledResponse->decodeResponseJson()['id'];
        $testParticipant = TestParticipant::where('test_take_id', $testTakeId)->get()
            ->filter(function ($participant) {
                return User::where('username', 's1@test-correct.nl')->first()->is($participant->user);
            });

        $uri = sprintf(
            'test_take/%d/test_participant/%d/heartbeat',
            $testTakeId,
            $testParticipant->first()->id
        );

        $response = $this->post(
            $uri,
            static::getStudentOneAuthRequestData([
                'ip_address' => $_SERVER['DB_HOST'],
            ])
        );

        $response->assertStatus(200);

        //start_take_participant
        $uri = sprintf(
            'http://test-correct.test/test_take/%d/test_participant/%d',
            $testTakeId,
            $testParticipant->first()->id
        );
        $startByStudentResponse = $this->put(
            $uri,
            self::getStudentOneAuthRequestData([
                'test_take_status_id' => 3,
            ])
        );

        $startByStudentResponse->assertStatus(200);



    }
}

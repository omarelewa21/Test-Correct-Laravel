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
//    use DatabaseTransactions;

    /** @test */
    public function it_should_return_a_heart_beat()
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

        // get all participants for this test;
        dd($scheduledResponse->decodeResponseJson()['id']);


        $response = $this->post(
            'test_take/4/test_participant/9/heartbeat',
            static::getStudentOneAuthRequestData([
                'ip_address' => $_SERVER['DB_HOST'],
            ])
        );

        $response->assertStatus(200);


    }
}

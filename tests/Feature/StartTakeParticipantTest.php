<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StartTakeParticipantTest extends TestCase
{
    //use DatabaseTransactions;


    /** @test */
    public function when_a_test_is_scheduled_a_student_can_participate()
    {
        $this->withoutExceptionHandling();
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
        ];

        $scheduledResponse = $this->post(
            'test_take',
            static::getTeacherOneAuthRequestData($newTestTakeData)
        );
        $scheduledResponse->assertStatus(200);

        $this->toetsActiveren($scheduledResponse->decodeResponseJson()['id']);
        $this->toetsInleveren($scheduledResponse->decodeResponseJson()['id']);
    }

    private function toetsActiveren($id) {
        $this->updateTestTakeStatus($id, 3);
    }

    private function toetsInleveren($id) {
       $this->updateTestTakeStatus($id, 9);
    }

    private function updateTestTakeStatus($testTakeId, $status) {
        $response = $this->put(
            sprintf(
                'test_take/%d',
                $testTakeId
            ),
            static::getTeacherOneAuthRequestData(
                ['test_take_status_id' => $status]
            )
        );
        $this->assertEquals(
            $status,
            $response->decodeResponseJson()['test_take_status_id']
        );

        $response->assertStatus(200);
    }


}

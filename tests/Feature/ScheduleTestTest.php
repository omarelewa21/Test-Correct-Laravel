<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ScheduleTestTest extends TestCase
{
    use DatabaseTransactions;


    /** @test */
    public function a_teacher_can_schedule_a_test()
    {
        $cntTestTaken = count($this->getListOfScheduledTests());

        $newTestTakeData = [
            'date'                => '19-07-2019',
            'period_id'           => 47,
            'invigilators'        => [529],
            'class_id'            => 204,
            'test_id'             => 1177,
            'weight'              => 1,
            'invigilator_note'    => '',
            'time_start'          => '2019-07-19 00:00:00',
            'retake'              => 0,
            'test_take_status_id' => 1,
            'school_classes'      => [204],
        ];


        $response = $this->post(
            'test_take',
            static::getAuthFiorettiRequestData($newTestTakeData)
        );

        $response->assertStatus(200);

        $this->assertEquals(
            ++$cntTestTaken,
            count($this->getListOfScheduledTests())
        );
    }

    /**
     * @return array
     */
    private function getListOfScheduledTests(): array
    {
        $listData = [
            'sort'    => '',
            'results' => 60,
            'page'    => 1,
            'filters' => '_method=POST&data%5BTestTake%5D%5Bperiod_id%5D=0&data%5BTestTake%5D%5Bretake%5D=-1&data%5BTestTake%5D%5Btime_start_from%5D=&data%5BTestTake%5D%5Btime_start_to%5D=',
            'order'   => ['time_start' => 'asc'],

            'filter' => [
                'test_take_status_id' => 1,
                'invigilator_id'      => 529,
            ],
        ];

        $response = $this->get(static::AuthFiorettiRequest('test_take', $listData));

        return $response->decodeResponseJson()['data'];
    }


}

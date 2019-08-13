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

        $response = $this->post(
            'test_take',
            static::getTeacherOneAuthRequestData($newTestTakeData)
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
                'invigilator_id'      => 1486,
            ],
        ];

        $response = $this->get(static::authTeacherOneGetRequest('test_take', $listData));

        return $response->decodeResponseJson()['data'];
    }


}

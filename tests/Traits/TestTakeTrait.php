<?php
/**
 * Created by PhpStorm.
 * User: martin
 * Date: 30/07/2019
 * Time: 13:04
 */

namespace Tests\Traits;

use tcCore\Test;
use Carbon\Carbon;

trait TestTakeTrait
{

	public function initDefaultTestTake($testId){
        $this->withoutExceptionHandling();
        $newTestTakeData = [
            'date'                => Carbon::now()->format('d-m-Y'),
            'period_id'           => 1,
            'invigilators'        => [1486],
            'class_id'            => 1,
            'test_id'             => $testId,
            'weight'              => 1,
            'invigilator_note'    => '',
            'time_start'          => Carbon::now()->format('Y-m-d H:i:s'),
            'retake'              => 0,
            'test_take_status_id' => 1,
            "school_classes"      => ["1"],
        ];
        $response = $this->post(
            'api-c/test_take',
            static::getTeacherOneAuthRequestData($newTestTakeData)
        );
        $response->assertStatus(200);
        return $response->decodeResponseJson()['id'];
	}

	public function initTestTakeForStudent($testTakeUuid,$testParticipantUuid){

        $data = [
            'test_take_status_id' => 3,
        ];
		$response = $this->put(
			sprintf('api-c/test_take/%s/test_participant/%s',$testTakeUuid,$testParticipantUuid),
            static::getStudentOneAuthRequestData($data)
		);
        $response->assertStatus(200);
	}

}
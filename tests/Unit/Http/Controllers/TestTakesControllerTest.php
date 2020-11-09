<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */

namespace Tests\Unit\Http\Controllers;

use tcCore\Http\Controllers\TestTakesController;
use tcCore\TestTake;
use tcCore\User;
use Tests\TestCase;

class TestTakesControllerTest extends TestCase
{

    use \Illuminate\Foundation\Testing\DatabaseTransactions;


    /** @test */
    public function surveillance_for_d1()
    {
        $filters = [
            "test_take_status_id" => "3",
            "invigilator_id"      => "1486",
            "mode"                => "list",
        ];
        $sorting = [
            "time_start" => "asc",
        ];
        $this->actingAs(User::whereUsername('d1@test-correct.nl')->first());

        (new TestTakesController())->show(TestTake::find(1));


    }

    /** @test */
    public function prutstest()
    {
        $response = $this->get(
            static::authTeacherOneGetRequest(
                'group_question_question/5/3',
                []
            )
        );
        dd($response);
    }


}
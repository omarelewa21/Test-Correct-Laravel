<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */

namespace Tests\Unit\Http\Controllers;

use tcCore\Http\Controllers\TestTakesController;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\TestTake;
use tcCore\User;
use tcCore\TestQuestion;
use Tests\TestCase;
use Tests\Traits\TestTrait;
use Tests\Traits\TestTakeTrait;
use Tests\Traits\GroupQuestionTrait;
use Tests\Traits\MultipleChoiceQuestionTrait;

class TestTakesControllerTest extends TestCase
{

    use \Illuminate\Foundation\Testing\DatabaseTransactions;
    use DatabaseTransactions;
    use TestTrait;
    use TestTakeTrait;
    use GroupQuestionTrait;
    use MultipleChoiceQuestionTrait;

    private $originalTestId;
    private $originalQuestionId;
    private $copyTestId;

    /** @test */
    public function calculateMaxScore()
    {
        $attributes = $this->getTestAttributes();
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForCarouselGroupQuestion($this->originalTestId);
        $testQuestionId = $this->createGroupQuestion($attributes);
        $groupTestQuestion = TestQuestion::find($testQuestionId);
        $attributes = $this->getAttributesForMultipleChoiceQuestion($this->originalTestId);
        for ($i=0; $i < 10; $i++) {     
            $this->createMultipleChoiceQuestionInGroup($attributes,$groupTestQuestion->uuid);
        }
        $this->createMultipleChoiceQuestion($attributes);
        $testTakeId = $this->initDefaultTestTake($this->originalTestId);
        $testTake = TestTake::find($testTakeId);
        $response = $this->get(static::authTeacherOneGetRequest('api-c/test_take_max_score/'.$testTake->uuid, []));
        $response->assertStatus(200);
        $response->dump();
    }

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
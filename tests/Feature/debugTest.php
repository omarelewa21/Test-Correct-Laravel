<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Answer;
use tcCore\User;
use tcCore\Test;
use tcCore\TestTake;
use tcCore\Question;
use tcCore\TestParticipant;
use tcCore\TestQuestion;
use tcCore\MulipleChoiceQuestion;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use tcCore\Traits\Dev\TestTrait;
use tcCore\Traits\Dev\TestTakeTrait;
use tcCore\Traits\Dev\GroupQuestionTrait;
use tcCore\Traits\Dev\MultipleChoiceQuestionTrait;
use Illuminate\Support\Facades\DB;
use tcCore\Http\Helpers\ActingAsHelper;

class debugTest extends TestCase
{
    use DatabaseTransactions;
    use TestTrait;
    use TestTakeTrait;
    use GroupQuestionTrait;
    use MultipleChoiceQuestionTrait;

    private $originalTestId;
    private $originalQuestionId;
    private $copyTestId;

     /** @test */
     public function debugDiscussWithCarousel(){
        $testTake = TestTake::find(20);
        $testTake->test_take_status_id = 6;
        $testTake->save();
        $url = '/api-c/test_take/05271d5b-3124-4213-9612-fe916f965870?';
        $response = $this->put(
            $url,
            static::getTeacherOneAuthRequestData(
                [   "test_take_status_id"=> 7,
                    "discussion_type"=> "ALL",
                ]
            )
        );
        $response->assertStatus(200);
     }

     /** @test */
     public function debugNumberOfQuestions(){
        $testTake = TestTake::find(8307);
        dump($testTake->test);
        $this->assertTrue(true);

     }

     

}
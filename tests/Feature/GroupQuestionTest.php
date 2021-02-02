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
use Tests\Traits\TestTrait;
use Tests\Traits\TestTakeTrait;
use Tests\Traits\GroupQuestionTrait;
use Tests\Traits\MultipleChoiceQuestionTrait;
use Illuminate\Support\Facades\DB;
use tcCore\Http\Helpers\ActingAsHelper;

class GroupQuestionTest extends TestCase
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
     public function can_create_test_and_group_question(){
        $attributes = $this->getTestAttributes();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForGroupQuestion($this->originalTestId);
        $this->createGroupQuestion($attributes);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
     }

     /** @test */
     public function can_create_test_and_group_carousel_question(){
        $attributes = $this->getTestAttributes();
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForCarouselGroupQuestion($this->originalTestId);
        $this->createGroupQuestion($attributes);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $testQuestion = $questions->first();
        $question = $testQuestion->question;
        $this->assertEquals('carousel', $question->groupquestion_type);
        $this->assertEquals(3,$question->number_of_subquestions);
     }

     /** @test */
     public function can_start_test_with_carousel(){
        $attributes = $this->getTestAttributes();
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForCarouselGroupQuestion($this->originalTestId);
        $testQuestionId = $this->createGroupQuestion($attributes);
        $groupTestQuestion = TestQuestion::find($testQuestionId);
        $attributes = $this->getAttributesForMultipleChoiceQuestion($this->originalTestId);
        for ($i=0; $i < 10; $i++) {     
            $this->createMultipleChoiceQuestionInGroup($attributes,$groupTestQuestion->uuid);
        }
        $testTakeId = $this->initDefaultTestTake($this->originalTestId);
        $testTake = TestTake::find($testTakeId);
        $this->toetsActiveren($testTake->uuid);       
        $user = User::where('username', 's1@test-correct.nl')->first();
        ActingAsHelper::getInstance()->setUser($user);
        $testParticipant = TestParticipant::where('user_id',$user->id)->where('test_take_id',$testTakeId)->first();
        $this->initTestTakeForStudent($testTake->uuid,$testParticipant->uuid);
        $answers = Answer::where('test_participant_id',$testParticipant->id)->get();
        $this->assertCount(3, $answers);
     }
       

}
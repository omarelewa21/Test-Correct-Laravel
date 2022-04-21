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
use Illuminate\Support\Str;

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
    private $groupTestQuestionId;
    private $groupTestQuestionUuid;

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

     /** @test */
     public function can_create_test_and_filled_group_carousel_question()
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
        $test = Test::find($this->originalTestId);
        $this->assertEquals(3, $test->question_count);
     }

     /** @test */
    public function modification_of_subject_of_groupquestion_leads_to_modifaction_of_subject_of_subquestions()
    {
        $this->setupScenario1();
        $test = Test::find($this->originalTestId);
        $test->subject_id = 6;
        $test->save();
        $groupTestQuestion = TestQuestion::find($this->groupTestQuestionId);
        foreach ($groupTestQuestion->question->groupQuestionQuestions as $groupQuestionQuestion){
            $this->assertEquals(6,$groupQuestionQuestion->question->subject_id);
        }
    }

    /** @test */
    public function adding_existing_question_to_group_leads_to_is_subquestion_true()
    {
        $this->setupScenario1();
        $openQuestion = Question::where('type','OpenQuestion')->firstOrFail();
        $this->assertEquals(0,$openQuestion->is_subquestion);
        $this->addExistingQuestionToGroup($openQuestion->id,$this->groupTestQuestionUuid);
        $groupTestQuestion = TestQuestion::find($this->groupTestQuestionId);
        $subQuestion = $groupTestQuestion->question->groupQuestionQuestions->filter(function ($groupQuestionQuestion, $key) {
            return $groupQuestionQuestion->question->getQuestionInstance()->type=='OpenQuestion';
        })->first();
        $this->assertEquals(1,$subQuestion->question->getQuestionInstance()->is_subquestion);
        $this->assertNotEquals($subQuestion->question->getQuestionInstance()->getKey(),$openQuestion->getKey());
    }

    private function setupScenario1(){
        $attributes = $this->getTestAttributes();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForGroupQuestion($this->originalTestId);
        $groupTestQuestionId = $this->createGroupQuestion($attributes);
        $this->groupTestQuestionId = $groupTestQuestionId;
        $groupTestQuestion = TestQuestion::find($groupTestQuestionId);
        $this->groupTestQuestionUuid = $groupTestQuestion->uuid;
        $attributes = $this->getAttributesForMultipleChoiceQuestion($this->originalTestId);
        for($i=0;$i<10;$i++){
            $this->createMultipleChoiceQuestionInGroup($attributes,$groupTestQuestion->uuid);
        }
        $this->checkScenario1Success('Test Title',$this->originalTestId);
    }



    private function checkScenario1Success($name,$testId){
        $tests = Test::where('name',$name)->get();
        $this->assertTrue(count($tests)==1);
        $questions = Test::find($testId)->testQuestions;
        $this->assertCount(1,$questions);
        $this->assertEquals('GroupQuestion',$questions->first()->question->type);
        $groupQuestion = $questions->first()->question;
        $subQuestions = $groupQuestion->groupQuestionQuestions;
        $this->assertCount(10,$subQuestions);
        $this->assertEquals('MultipleChoiceQuestion',$subQuestions->first()->question->type);
    }

}
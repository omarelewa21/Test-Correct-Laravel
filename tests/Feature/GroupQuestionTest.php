<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\User;
use tcCore\Test;
use tcCore\Question;
use tcCore\MulipleChoiceQuestion;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\TestTrait;
use Tests\Traits\GroupQuestionTrait;
use Illuminate\Support\Facades\DB;

class GroupQuestionTest extends TestCase
{
    use DatabaseTransactions;
    use TestTrait;
    use GroupQuestionTrait;

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
        unset($attributes['school_classes']);
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
       

}
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
use tcCore\Traits\Dev\TestTrait;
use tcCore\Traits\Dev\DrawingQuestionTrait;
use Illuminate\Support\Facades\DB;

class DrawingQuestionTest extends TestCase
{
    use DatabaseTransactions;
    use TestTrait;
    use DrawingQuestionTrait;

    private $originalTestId;
    private $originalQuestionId;
    private $copyTestId;

     /** @test */
     public function can_create_test_and_drawing_question(){
        $attributes = $this->getTestAttributes();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForDrawingQuestion($this->originalTestId);
        $this->createDrawingQuestion($attributes);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
     }

    /** @test */
    public function can_edit_drawing_question(){
        $attributes = $this->getTestAttributes();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForDrawingQuestion($this->originalTestId);
        $this->createDrawingQuestion($attributes);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $attributes = $this->getAttributesForEditDrawingQuestion($this->originalTestId);
        $this->editDrawingQuestion($questions->first()->uuid,$attributes);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertEquals($this->getDrawing2(),$questions->first()->question->answer);
    }

    /** @test */
    public function can_edit_drawing_question_not_drawing(){
        $attributes = $this->getTestAttributes();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForDrawingQuestion($this->originalTestId);
        $this->createDrawingQuestion($attributes);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $attributes = $this->getAttributesForEditDrawingQuestion2($this->originalTestId);
        $this->editDrawingQuestion($questions->first()->uuid,$attributes);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertEquals('joepie',$questions->first()->question->getQuestionHtml());
    }

    /** @test */
    public function can_edit_drawing_question_drawing_and_question(){
        $attributes = $this->getTestAttributes();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForDrawingQuestion($this->originalTestId);
        $this->createDrawingQuestion($attributes);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $attributes = $this->getAttributesForEditDrawingQuestion3($this->originalTestId);
        $this->editDrawingQuestion($questions->first()->uuid,$attributes);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertEquals('joepie',$questions->first()->question->getQuestionHtml());
    }

    /** @test */
    public function can_edit_drawing_question_in_copy(){
        $attributes = $this->getTestAttributes();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForDrawingQuestion($this->originalTestId);
        $this->createDrawingQuestion($attributes);
        $this->duplicateTest($this->originalTestId);
        $questions = Test::find($this->copyTestId)->testQuestions;
        $attributes = $this->getAttributesForEditDrawingQuestion3($this->copyTestId);
        $this->editDrawingQuestion($questions->first()->uuid,$attributes);
        $questions = Test::find($this->copyTestId)->testQuestions;
        $this->assertEquals($this->getDrawing2(),$questions->first()->question->answer);
    }

}
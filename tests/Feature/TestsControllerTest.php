<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Test;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\MultipleChoiceQuestionTrait;
use Tests\Traits\TestTrait;

class TestsControllerTest extends TestCase
{
    use DatabaseTransactions;
    use TestTrait;
    use MultipleChoiceQuestionTrait;

    private $originalTestId;
    private $originalQuestionId;
    private $copyTestId;

    /** @test */

    public function it_should_copy_questionsWhenModifyingTestSubjectId()
    {
        $this->setupScenario1();
        $tests = Test::where('name','TToets van GM1')->get();
        $this->assertTrue(count($tests)==1);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $originalQuestionArray = $questions->pluck('question_id')->toArray();
        $tests = Test::where('name','Kopie #1 TToets van GM1')->get();
        $this->assertTrue(count($tests)==1);
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $this->assertEquals(6,$copyQuestions->first()->question->subject_id);
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)==0);
        $copyTest = Test::find($this->copyTestId);
        $copyTest->subject_id = 1;
        $copyTest->save();
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)>0);
        $this->assertEquals(1,$copyQuestions->first()->question->subject_id);
    }

    /** @test */
    public function it_should_copy_questionsWhenModifyingTestEducationLevelYear()
    {
        $this->setupScenario1();
        $tests = Test::where('name','TToets van GM1')->get();
        $this->assertTrue(count($tests)==1);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $originalQuestionArray = $questions->pluck('question_id')->toArray();
        $tests = Test::where('name','Kopie #1 TToets van GM1')->get();
        $this->assertTrue(count($tests)==1);
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $this->assertEquals(6,$copyQuestions->first()->question->subject_id);
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)==0);
        $copyTest = Test::find($this->copyTestId);
        $copyTest->education_level_year = 5;
        $copyTest->save();
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)>0);
        $this->assertEquals(5,$copyQuestions->first()->question->education_level_year);
    }


    private function setupScenario1(){
        $attributes = $this->getAttributesForTest1();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForQuestion1($this->originalTestId);
        $this->createMultipleChoiceQuestion($attributes);
        $this->duplicateTest($this->originalTestId);
    }


    private function getAttributesForTest1(){

        return $this->getTestAttributes([
            'name'                   => 'TToets van GM1',
            'abbreviation'           => 'TTGM1',
            'subject_id'             => '6',
            'introduction'           => 'intro',
            "education_level_year"   => 2
        ]);

    }

    private function getAttributesForEditQuestion1($testId){
        $attributes = array_merge($this->getAttributesForQuestion1($testId),[   "answers"=> [
            [
                "answer"=> "Juist",
                "score"=> 0,
                "order"=> 0
            ],
            [
                "answer"=> "Onjuist",
                "score"=> "5",
                "order"=> 0
            ]
        ],
        ]);
        unset($attributes["test_id"]);
        return $attributes;
    }

    private function getAttributesForQuestion1($testId){
        return [
            "type"=> "MultipleChoiceQuestion",
            "score"=> "5",
            "question"=> "<p>GM1</p> ",
            "order"=> 0,
            "maintain_position"=> "0",
            "discuss"=> "1",
            "subtype"=> "TrueFalse",
            "decimal_score"=> "0",
            "add_to_database"=> 1,
            "attainments"=> [
            ],
            "note_type"=> "NONE",
            "is_open_source_content"=> 1,
            "answers"=> [
                [
                    "answer"=> "Juist",
                    "score"=> "5",
                    "order"=> 0
                ],
                [
                    "answer"=> "Onjuist",
                    "score"=> 0,
                    "order"=> 0
                ]
            ],
            "tags"=> [
            ],
            "rtti"=> "R",
            "bloom"=> "Onthouden",
            "miller"=> "Weten",
            "test_id"=> $testId,
            "closeable"=> 0,
            "subject_id"=> 6,
            "education_level_year"=> 2
        ];
    }
}

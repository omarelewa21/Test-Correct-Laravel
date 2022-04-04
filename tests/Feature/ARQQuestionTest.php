<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\TestQuestion;
use tcCore\Test;
use tcCore\MulipleChoiceQuestion;
use Tests\TestCase;
use tcCore\Traits\Dev\TestTrait;
use tcCore\Traits\Dev\MultipleChoiceQuestionTrait;
use tcCore\Traits\Dev\GroupQuestionTrait;


class ARQQuestionTest extends TestCase
{
    use DatabaseTransactions;
    use TestTrait;
    use MultipleChoiceQuestionTrait;
    use GroupQuestionTrait;

    private $originalTestId;
    private $originalQuestionId;
    private $copyTestId;



    /** @test */
    public function can_create_test_and_mc_arq_question(){
        $attributes = $this->getAttributesForTest7();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForARQQuestion($this->originalTestId);
        $this->createMultipleChoiceQuestion($attributes);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $this->assertTrue(!is_null($questions->first()->question->multipleChoiceQuestionAnswers));
    }

    /** @test */
    public function can_create_test_and_mc_arq_question_with_answers(){
        $attributes = $this->getAttributesForTest7();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForARQQuestion($this->originalTestId);
        $this->createMultipleChoiceQuestion($attributes);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $this->assertTrue(!is_null($questions->first()->question->multipleChoiceQuestionAnswers));
        $this->assertEquals(5, count($questions->first()->question->multipleChoiceQuestionAnswers));
    }

    /** @test */
    public function can_create_test_and_mc_arq_question_with_answers_containing_zero_value(){
        $attributes = $this->getAttributesForTest7();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForARQQuestionWithZeroAnswer($this->originalTestId);
        $this->createMultipleChoiceQuestion($attributes);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $this->assertTrue(!is_null($questions->first()->question->multipleChoiceQuestionAnswers));
        $this->assertEquals(5, count($questions->first()->question->multipleChoiceQuestionAnswers));
    }

    /** @test */
    public function can_edit_mc_arq_question(){
        $attributes = $this->getAttributesForTest7();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForARQQuestion($this->originalTestId);
        $this->createMultipleChoiceQuestion($attributes);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $attributes = array_merge($attributes,["question"=>'joepie']);
        $this->editMultipleChoiceQuestion(Test::find($this->originalTestId)->testQuestions->first()->uuid,$attributes);
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertEquals('joepie', $questions->first()->question->getQuestionHtml());
    }

    /** @test */
    public function it_can_update_arq_answers_in_group()
    {
        $this->setupScenario1();
        $this->originalAndCopyShareGroupQuestion();
        $attributes = $this->getAttributesForEditQuestion2($this->copyTestId);
        $copyGroupTestQuestion = Test::find($this->copyTestId)->testQuestions->first();
        $copyQuestion = Test::find($this->copyTestId)->testQuestions->first()->question->groupQuestionQuestions->first();
        $this->editMultipleChoiceQuestionInGroup($copyGroupTestQuestion->uuid,$copyQuestion->uuid,$attributes);
        $this->originalAndCopyDifferFromGroupQuestion();
        $copyQuestion = Test::find($this->copyTestId)->testQuestions->first()->question->groupQuestionQuestions->first();
        $mcAnswer = $copyQuestion->question->multipleChoiceQuestionAnswers()->first();
        $this->assertEquals('30',$mcAnswer->score);
    }

    private function setupScenario1(){
        $attributes = $this->getTestAttributes();
        unset($attributes['school_classes']);
        $this->createTLCTest($attributes);
        $attributes = $this->getAttributesForGroupQuestion($this->originalTestId);
        $groupTestQuestionId = $this->createGroupQuestion($attributes);
        $groupTestQuestion = TestQuestion::find($groupTestQuestionId);
        $attributes = $this->getAttributesForARQQuestion($this->originalTestId);
        $this->createMultipleChoiceQuestionInGroup($attributes,$groupTestQuestion->uuid);
        $this->duplicateTest($this->originalTestId);

        $this->checkScenario1Success('Test Title',$this->originalTestId);
        $this->checkScenario1Success('Kopie #1 Test Title',$this->copyTestId);

    }

    private function checkScenario1Success($name,$testId){
        $tests = Test::where('name',$name)->get();
        $this->assertTrue(count($tests)==1);
        $questions = Test::find($testId)->testQuestions;
        $this->assertCount(1,$questions);
        $this->assertEquals('GroupQuestion',$questions->first()->question->type);
        $groupQuestion = $questions->first()->question;
        $subQuestions = $groupQuestion->groupQuestionQuestions;
        $this->assertCount(1,$subQuestions);
        $this->assertEquals('MultipleChoiceQuestion',$subQuestions->first()->question->type);
    }

    private function getAttributesForTest7(){

        return $this->getTestAttributes([
            'name'                   => 'TToets van GM7',
            'abbreviation'           => 'TTGM7',
            'subject_id'             => '6',
            'introduction'           => 'intro',
        ]);

    }

    private function getGetAttributes($testId){
        return [
            "filter"=> [
                "test_id"=> $testId
            ],
            "mode"=> "all",
            "order"=> [
                "order"=> "asc"
            ]
        ];
    }

    private function getAttributesForEditQuestion2($testId){
        return array_merge($this->getAttributesForARQQuestion($testId),["answers"=> [
            [
                "score"=> "30"
            ],
            [
                "score"=> "10"
            ],
            [
                "score"=> "10"
            ],
            [
                "score"=> "0"
            ],
            [
                "score"=> "0"
            ]
        ]]);
    }




    private function getAttributesForARQQuestion($testId){
        return [
            "type"=> "MultipleChoiceQuestion",
            "score"=> "150",
            "question"=> "<p>GM7</p> ",
            "order"=> 0,
            "maintain_position"=> "0",
            "discuss"=> "1",
            "subtype"=> "ARQ",
            "decimal_score"=> "0",
            "add_to_database"=> 1,
            "attainments"=> [
            ],
            "note_type"=> "NONE",
            "is_open_source_content"=> 1,
            "answers"=> [
                [
                    "score"=> "10"
                ],
                [
                    "score"=> "20"
                ],
                [
                    "score"=> "30"
                ],
                [
                    "score"=> "40"
                ],
                [
                    "score"=> "50"
                ]
            ],
            "tags"=> [
            ],
            "rtti"=> "R",
            "bloom"=> "Onthouden",
            "miller"=> "Weten",
            "test_id"=> $testId,
            'closeable'=> 0,
        ];
    }

    private function getAttributesForARQQuestionWithZeroAnswer($testId){
        return [
            "type"=> "MultipleChoiceQuestion",
            "score"=> "150",
            "question"=> "<p>GM7</p> ",
            "order"=> 0,
            "maintain_position"=> "0",
            "discuss"=> "1",
            "subtype"=> "ARQ",
            "decimal_score"=> "0",
            "add_to_database"=> 1,
            "attainments"=> [
            ],
            "note_type"=> "NONE",
            "is_open_source_content"=> 1,
            "answers"=> [
                [
                    "score"=> "10"
                ],
                [
                    "score"=> "20"
                ],
                [
                    "score"=> "30"
                ],
                [
                    "score"=> "40"
                ],
                [
                    "score"=> "0"
                ]
            ],
            "tags"=> [
            ],
            "rtti"=> "R",
            "bloom"=> "Onthouden",
            "miller"=> "Weten",
            "test_id"=> $testId,
            'closeable'=> 0,
        ];
    }


}
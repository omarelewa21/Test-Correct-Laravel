<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use tcCore\Test;
use tcCore\MatchingQuestion;

class MatchingQuestionTest extends TestCase
{
    use DatabaseTransactions;

    private $originalTestId;

    /** @test */
    public function a_teacher_can_add_a_matching_question_to_a_test()
    {
        $this->withExceptionHandling();
        $test = $this->createNewTest();

        $addQuestionResponse = $this->post(
            'api-c/test_question',
            static::getTeacherOneAuthRequestData([
                'type'                   => 'MatchingQuestion',
                'score'                  => '5',
                'question'               => '<p>abcdef</p>\r\n',
                'order'                  => 0,
                'maintain_position'      => '0',
                'discuss'                => '1',
                'subtype'                => 'Matching',
                'decimal_score'          => '0',
                'add_to_database'        => 1,
                'attainments'            => [],
                'note_type'              => 'NONE',
                'is_open_source_content' => 1,
                'tags'                   => [],
                'rtti'                   => null,
                'test_id'                => $test['id'],
                'answers'               =>[
                    [
                        'order'=> '1',
                        'left'=> 'aa',
                        'right'=> 'aa',
                    ],
                    [
                        'order'=> '2',
                        'left'=> 'a1',
                        'right'=> 'a12',
                    ]
                ]
            ])
        );



        $addQuestionResponse->assertStatus(200);
        $this->deleteTest($test);
    }


    private function createNewTest($overrides = [])
    {
        $attributes = array_merge([
            'name'                   => 'Test Title 1abc',
            'abbreviation'           => 'TT',
            'test_kind_id'           => '3',
            'subject_id'             => '1',
            'education_level_id'     => '1',
            'education_level_year'   => '1',
            'period_id'              => '1',
            'shuffle'                => '0',
            'is_open_source_content' => '1',
            'introduction'           => 'Hello this is the intro txt',
        ], $overrides);

        $response = $this->post(
            'api-c/test',
            static::getTeacherOneAuthRequestData($attributes)
        );

        return $response->decodeResponseJson();
        $this->deleteTest($test);
    }

    /** @test */
    public function it_should_create_a_matching_question_with_answers()
    {
        $this->createTLCTest();

        $addQuestionResponse = $this->post(
            'api-c/test_question',
            static::getTeacherOneAuthRequestData([
                'type'                   => 'MatchingQuestion',
                'score'                  => '5',
                'question'               => '<p>abcdef</p>\r\n',
                'order'                  => 0,
                'maintain_position'      => '0',
                'discuss'                => '1',
                'subtype'                => 'Matching',
                'decimal_score'          => '0',
                'add_to_database'        => 1,
                'attainments'            => [],
                'note_type'              => 'NONE',
                'is_open_source_content' => 1,
                'tags'                   => [],
                'rtti'                   => null,
                'test_id'                => $this->originalTestId,
                'answers'               =>[
                    [
                        'order'=> '1',
                        'left'=> 'aa',
                        'right'=> 'aa',
                    ],
                    [
                        'order'=> '2',
                        'left'=> 'a1',
                        'right'=> 'a12',
                    ]
                ]
            ])
        );
        $questionId = $addQuestionResponse->decodeResponseJson()['id'];
        $questions = Test::find($this->originalTestId)->testQuestions;
        $this->assertTrue(count($questions)==1);
        $this->assertTrue(!is_null($questions->first()->question->matchingQuestionAnswers));
    
        
    }

     private function createTLCTest(){
        $response = $this->post(
            'api-c/test',
            static::getTeacherOneAuthRequestData(
                $this->getAttributesForTest4()
            )
        );
        $response->assertStatus(200);
        $testId = $response->decodeResponseJson()['id'];
        $this->originalTestId = $testId;
    }

    

    private function getAttributesForTest4(){

        return $this->getAttributes([
            'name'                   => 'TToets van GM4',
            'abbreviation'           => 'TTGM4',
            'subject_id'             => '6',
            'introduction'           => 'intro',
        ]);
    
    }

    private function getAttributes($overrides = [])
    {
        return array_merge([
            'name'                   => 'Test Title',
            'abbreviation'           => 'TT',
            'test_kind_id'           => '3',
            'subject_id'             => '1',
            'education_level_id'     => '1',
            'education_level_year'   => '1',
            'period_id'              => '1',
            'shuffle'                => '0',
            'is_open_source_content' => '1',
            'introduction'           => 'Hello this is the intro txt',
        ], $overrides);
    }

    
    
}

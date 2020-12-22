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
    //use DatabaseTransactions;

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
        $this->assertTrue(!is_null($questions->first()->question->getQuestionInstance()->matchingQuestionAnswers));
        
        // $this->createTLCTest();
        // $attributes = $this->getAttributesForQuestion4($this->originalTestId);
        // $this->createQuestion($attributes);
        // $tests = Test::where('name','TToets van GM4')->get();
        // $this->assertTrue(count($tests)==1);
        // $questions = Test::find($this->originalTestId)->testQuestions;
        // $this->assertTrue(count($questions)==1);
        // $this->assertTrue(!is_null($questions->first()->question->getQuestionInstance()->matchingQuestionAnswers));
        
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

    private function createQuestion($attributes){
        $response = $this->post(
            'api-c/test_question',
            static::getTeacherOneAuthRequestData(
                $attributes
            )
        );
        $response->assertStatus(200);
        $questionId = $response->decodeResponseJson()['id'];
        $this->originalQuestionId = $questionId;
    }

    private function editQuestion($uuid,$attributes){
        $response = $this->put(
            'api-c/test_question/'.$uuid,
            static::getTeacherOneAuthRequestData(
                $attributes
            )
        );
        $response->assertStatus(200);
    }

    private function duplicateTest($testId){
        $test = Test::find($testId);
        $response = $this->post(
            '/api-c/test/'.$test->uuid.'/duplicate',
            static::getTeacherOneAuthRequestData(
                ['status'=>0]
            )
        );
        $response->assertStatus(200);
        $testId = $response->decodeResponseJson()['id'];
        $this->copyTestId = $testId;
    }

    private function clearDB(){
        $tests = Test::where('name','TToets van GM4')->get();
        foreach ($test as $key => $test) {
            $questions = $test->questions;
            foreach ($questions as $key => $question) {
                # code...
            }
        }
        $sql = "delete from tests where name='TToets van GM4'";
        DB::delete($sql);
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

    private function getAttributesForEditQuestion4($testId){
        $attributes = array_merge($this->getAttributesForTest4($testId),[   "question"=> "<p>GM42</p>",
                                                                            "answers"=> [
                                                                                [
                                                                                    "order"=> "1",
                                                                                    "left"=> "aa2",
                                                                                    "right"=> "bb2"
                                                                                ],
                                                                                [
                                                                                    "order"=> "2",
                                                                                    "left"=> "cc2",
                                                                                    "right"=> "dd2"
                                                                                ],
                                                                                [
                                                                                    "order"=> "3",
                                                                                    "left"=> "ee2",
                                                                                    "right"=> "ff2"
                                                                                ],
                                                                                [
                                                                                    "order"=> "3",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "4",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "5",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "6",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "7",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "8",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "9",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "10",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "11",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "12",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "13",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "14",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "15",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "16",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "17",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "18",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "19",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "20",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "21",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "22",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "23",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "24",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "25",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "26",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "27",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "28",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "29",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "30",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "31",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "32",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "33",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "34",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "35",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "36",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "37",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "38",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "39",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "40",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "41",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "42",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "43",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "44",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "45",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "46",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "47",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "48",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ],
                                                                                [
                                                                                    "order"=> "49",
                                                                                    "left"=> "",
                                                                                    "right"=> ""
                                                                                ]
                                                                            ],

                                                                        ]);
        unset($attributes["test_id"]);
        return $attributes;
    }

    private function getAttributesForQuestion4($testId){
        return [    
                    "type"=> "MatchingQuestion",
                    "score"=> "5",
                    "question"=> "<p>GM4</p> ",
                    "order"=> 0,
                    "maintain_position"=> "0",
                    "discuss"=> "1",
                    "subtype"=> "Classify",
                    "decimal_score"=> "0",
                    "add_to_database"=> 1,
                    "attainments"=> [
                    ],
                    "note_type"=> "NONE",
                    "is_open_source_content"=> 1,
                    "answers"=> [
                        [
                            "order"=> "1",
                            "left"=> "aa",
                            "right"=> "bb"
                        ],
                        [
                            "order"=> "2",
                            "left"=> "cc",
                            "right"=> "dd"
                        ],
                        [
                            "order"=> "3",
                            "left"=> "ee",
                            "right"=> "ff"
                        ],
                        [
                            "order"=> "3",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "4",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "5",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "6",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "7",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "8",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "9",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "10",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "11",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "12",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "13",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "14",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "15",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "16",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "17",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "18",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "19",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "20",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "21",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "22",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "23",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "24",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "25",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "26",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "27",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "28",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "29",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "30",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "31",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "32",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "33",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "34",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "35",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "36",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "37",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "38",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "39",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "40",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "41",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "42",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "43",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "44",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "45",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "46",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "47",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "48",
                            "left"=> "",
                            "right"=> ""
                        ],
                        [
                            "order"=> "49",
                            "left"=> "",
                            "right"=> ""
                        ]
                    ],
                    "tags"=> [
                    ],
                    "rtti"=> "R",
                    "bloom"=> "Onthouden",
                    "miller"=> "Weten",
                    "test_id"=> $testId,
                ];
    }
    
}

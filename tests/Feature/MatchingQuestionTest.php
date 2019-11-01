<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MatchingQuestionTest extends TestCase
{
//    use RefreshDatabase;


    /** @test */
    public function a_teacher_can_add_a_matching_question_to_a_test()
    {
        $this->withExceptionHandling();
        $test = $this->createNewTest();

        $addQuestionResponse = $this->post(
            '/test_question',
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
            '/test',
            static::getTeacherOneAuthRequestData($attributes)
        );

        return $response->decodeResponseJson();
        $this->deleteTest($test);
    }


}

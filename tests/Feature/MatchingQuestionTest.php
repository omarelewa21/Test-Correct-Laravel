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
    use DatabaseTransactions;


    /** @test */
    public function a_teacher_can_add_a_matching_question_to_a_test()
    {
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
            ])
        );

        $addQuestionResponse->assertStatus(200);

        $questionId = $addQuestionResponse->decodeResponseJson()['id'];

        $options = [
            [
                'order'=> '1',
                'answer'=> 'aa',
                'type'=> 'left',
            ],
            [
                'order'=> '1',
                'answer'=> 'aa',
                'type'=> 'right',
            ],
            [
                'order'=> '2',
                'answer'=> 'a1',
                'type'=> 'left',
            ],
            [
                'order'=> '2',
                'answer'=> 'a12',
                'type'=> 'right',
            ]
        ];

        foreach ($options as $option) {
            $addMatchingOptionsResponse = $this->post(
                sprintf('test_question/%d/matching_question_answer', $questionId),
                static::getTeacherOneAuthRequestData($option)
            );
            $this->assertEquals(
                $option['answer'],
                $addMatchingOptionsResponse->decodeResponseJson()['answer']
            );
             $this->assertEquals(
                $option['type'],
                $addMatchingOptionsResponse->decodeResponseJson()['type']
             );

            $addMatchingOptionsResponse->assertStatus(200);
        }

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
    }


}

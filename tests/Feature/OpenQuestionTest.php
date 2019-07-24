<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OpenQuestionTest extends TestCase
{
    use DatabaseTransactions;


    /** @test */
    public function a_teacher_can_add_a_open_question_to_a_test_o()
    {
        $test = $this->createNewTest();

        $response = $this->post(
            '/test_question',
            static::getTeacherOneAuthRequestData([
                'question'               => '<p>aa</p>',
                'answer'                 => '<p>bb</p>',
                'type'                   => 'OpenQuestion',
                'score'                  => 5,
                'order'                  => 0,
                'subtype'                => 'short',
                'maintain_position'      => 0,
                'discuss'                => 1,
                'decimal_score'          => 0,
                'add_to_database'        => 1,
                'attainments'            => [],
                'note_type'              => 'NONE',
                'is_open_source_content' => 1,
                'tags'                   => [],
                'rtti'                   => 'R',
                'test_id'                => $test['id'],
            ])
        );
        $response->assertStatus(200);
    }

    /** @test */
    public function a_teacher_can_add_a_open_question_to_a_test_with_rtti_is_null()
    {
        $test = $this->createNewTest();

        $response = $this->post(
            '/test_question',
            static::getTeacherOneAuthRequestData([
                'question'               => '<p>aa</p>',
                'answer'                 => '<p>bb</p>',
                'type'                   => 'OpenQuestion',
                'score'                  => 5,
                'order'                  => 0,
                'subtype'                => 'short',
                'maintain_position'      => 0,
                'discuss'                => 1,
                'decimal_score'          => 0,
                'add_to_database'        => 1,
                'attainments'            => [],
                'note_type'              => 'NONE',
                'is_open_source_content' => 1,
                'tags'                   => [],
                'rtti'                   => null,
                'test_id'                => $test['id'],
            ])
        );
//        print_r($response->decodeResponseJson());die;

        $response->assertStatus(200);

    }

    /** @test */
    public function a_teacher_cannot_add_a_open_question_to_a_test_with_an_invalid_rtti_value()
    {
        $test = $this->createNewTest();

        $response = $this->post(
            '/test_question',
            static::getTeacherOneAuthRequestData([
                'question'               => '<p>aa</p>',
                'answer'                 => '<p>bb</p>',
                'type'                   => 'OpenQuestion',
                'score'                  => 5,
                'order'                  => 0,
                'subtype'                => 'short',
                'maintain_position'      => 0,
                'discuss'                => 1,
                'decimal_score'          => 0,
                'add_to_database'        => 1,
                'attainments'            => [],
                'note_type'              => 'NONE',
                'is_open_source_content' => 1,
                'tags'                   => [],
                'rtti'                   => 'invalid',
                'test_id'                => $test['id'],
            ])
        );

        $response->assertStatus(422);
        
    }

    /** @test */
    public function a_teacher_can_add_a_open_question_to_a_test_with_null_string_as_rtti_value()
    {
        $test = $this->createNewTest();
        $response = $this->post(
            '/test_question',
            static::getTeacherOneAuthRequestData([
                'question'               => '<p>aa</p>',
                'answer'                 => '<p>bb</p>',
                'type'                   => 'OpenQuestion',
                'score'                  => 5,
                'order'                  => 0,
                'subtype'                => 'short',
                'maintain_position'      => 0,
                'discuss'                => 1,
                'decimal_score'          => 0,
                'add_to_database'        => 1,
                'attainments'            => [],
                'note_type'              => 'NONE',
                'is_open_source_content' => 1,
                'tags'                   => [],
                'rtti'                   => '',
                'test_id'                => $test['id'],
            ])
        );

        $response->assertStatus(200);
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

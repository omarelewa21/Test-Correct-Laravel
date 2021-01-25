<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RetrieveTestTest extends TestCase
{
    use DatabaseTransactions;


    /** @test */
    public function get_test_by_uuid()
    {
        $response = $this->get(
            static::authTeacherCarloGetRequest(
                'api-c/test/26c921c2-b477-43bb-ae57-44fb711c9009',
                []
            )
        );
        dump($response->decodeResponseJson());
        $response->assertStatus(200);
    }

    /** @test */
    public function get_test_question_by_uuid()
    {
        $response = $this->get(
            static::authTeacherCarloGetRequest(
                'api-c/test_question/724f809e-0121-450c-bab6-f580d4c149ba',
                []
            )
        );
        dump($response->decodeResponseJson());
        $response->assertStatus(200);
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

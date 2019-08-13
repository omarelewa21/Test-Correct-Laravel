<?php
/**
 * Created by PhpStorm.
 * User: martin
 * Date: 30/07/2019
 * Time: 13:04
 */

namespace Tests\Traits;


trait TestTrait
{
    private function getTestAttributes(array $overrides = []): array
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
            "school_classes"         => ["1"],
        ], $overrides);
    }

    private function addTestAndReturnTestId(array $overrides = []): int
    {
        $response = $this->post(
            '/test',
            static::getTeacherOneAuthRequestData(
                $this->getTestAttributes()
            )
        );

        $response->assertStatus(200);

        return $response->decodeResponseJson()['id'];
    }
}
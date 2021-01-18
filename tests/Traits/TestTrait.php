<?php
/**
 * Created by PhpStorm.
 * User: martin
 * Date: 30/07/2019
 * Time: 13:04
 */

namespace Tests\Traits;

use tcCore\Test;

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

    private function getTestQuestionsByGet($attributes){
        $response = $this->get(static::authTeacherOneGetRequest('/api-c/test_question', $attributes));
        $response->assertStatus(200);

        return $response->decodeResponseJson();
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

    private function createTLCTest($attributes){
        $response = $this->post(
            'api-c/test',
            static::getTeacherOneAuthRequestData(
                $attributes
            )
        );
        $response->assertStatus(200);
        $testId = $response->decodeResponseJson()['id'];
        $this->originalTestId = $testId;
    }

    private function originalAndCopyShareQuestion(){
        $questions = Test::find($this->originalTestId)->testQuestions;
        $originalQuestionArray = $questions->pluck('question_id')->toArray();
        $copyQuestions = Test::find($this->copyTestId)->testQuestions;
        $copyQuestionArray = $copyQuestions->pluck('question_id')->toArray();
        $result = array_diff($originalQuestionArray, $copyQuestionArray);
        $this->assertTrue(count($result)==0);
    }
}
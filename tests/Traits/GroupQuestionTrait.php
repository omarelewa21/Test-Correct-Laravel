<?php
/**
 * Created by PhpStorm.
 * User: martin
 * Date: 30/07/2019
 * Time: 13:05
 */

namespace Tests\Traits;


use Tests\Feature\CreateQuestionGroupWithinTestTest;


trait GroupQuestionTrait
{


    protected function addQuestionGroupAndReturnId(int $testId, array $overrides = []): int
    {
        $attributes = array_merge([
            'name'              => 'Vraaggroup naam',
            'question'          => 'VraagGroup Omschrijving',
            'order'             => 0,
            'shuffle'           => '0',
            'maintain_position' => '0',
            'discuss'           => 0,
            'add_to_database'   => '1',
            'test_id'           => $testId,
            'type'              => 'GroupQuestion',
            'attainments'       => [],
        ], $overrides);

        $response = $this->post(
            'test_question',
            static::getTeacherOneAuthRequestData($attributes)
        );

        $response->assertStatus(200);

        return $response->decodeResponseJson()['id'];
    }

    private function createGroupQuestion($attributes){
        $response = $this->post(
            'api-c/test_question',
            static::getTeacherOneAuthRequestData(
                $attributes
            )
        );
        $response->assertStatus(200);
        $testQuestionId = $response->decodeResponseJson()['id'];
        $this->originalQuestionId = $testQuestionId;
        $this->originalGroupQuestionId = $testQuestionId;
        return $testQuestionId;
    }

    private function editGroupQuestion($uuid,$attributes){
        $response = $this->put(
            'api-c/test_question/'.$uuid,
            static::getTeacherOneAuthRequestData(
                $attributes
            )
        );
        $response->assertStatus(200);
    }


    private function getAttributesForGroupQuestion($testId){
        return [
                    "name"=> "vraag groep van GM",
                    "question"=> "",
                    "order"=> 0,
                    "shuffle"=> "0",
                    "maintain_position"=> "0",
                    "discuss"=> 0,
                    "add_to_database"=> 1,
                    "test_id"=> $testId,
                    "type"=> "GroupQuestion",
                    "attainments"=> [
                    ]
                ];
    }
}
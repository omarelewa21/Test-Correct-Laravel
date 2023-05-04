<?php
/**
 * Created by PhpStorm.
 * User: martin
 * Date: 30/07/2019
 * Time: 13:05
 */

namespace Tests\Traits;


use tcCore\User;
use Tests\Feature\CreateQuestionGroupWithinTestTest;


trait GroupQuestionTrait
{


    protected function addQuestionGroupAndReturnId(int $testId, array $overrides = []): int
    {
        $attributes = array_merge([
            'name'              => 'Vraaggroep naam',
            'question'          => 'VraagGroep Omschrijving',
            'order'             => 0,
            'shuffle'           => '0',
            'maintain_position' => '0',
            'discuss'           => 0,
            'add_to_database'   => '1',
            'test_id'           => $testId,
            'type'              => 'GroupQuestion',
            'attainments'       => [],
            "closeable"=> 0,
        ], $overrides);

        $response = $this->post(
            'api-c/test_question',
            static::getTeacherOneAuthRequestData($attributes)
        );
        $response->assertStatus(200);

        return $response->decodeResponseJson()['id'];
    }

    protected function addExistingQuestionToGroup($questionId,$groupQuestionId)
    {
        $attributes =[
            "group_question_id" => $groupQuestionId,
            "order"             => 0,
            "maintain_position" => 0,
            "discuss"           => 1,
            "closeable"         => 0,
            "question_id"       => $questionId,
            "owner_id"          => $groupQuestionId,
        ];

        $response = $this->post(
            'api-c/group_question_question/'.$groupQuestionId,
            static::getTeacherOneAuthRequestData($attributes)
        );
        $response->assertStatus(200);
    }

    private function createGroupQuestion($attributes, $user = null){
        if ($user == null) {
            $user = User::where('username', 'd1@test-correct.nl')->first();
        }
        $response = $this->post(
            'api-c/test_question',
            static::getUserAuthRequestData(
                $user,
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

    private function getAttributesForCarouselGroupQuestion($testId, $numberOfSubquestions = 3){
        $attributes = array_merge($this->getAttributesForGroupQuestion($testId), ['groupquestion_type'=>'carousel','number_of_subquestions'=>$numberOfSubquestions]);
        return $attributes;
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
                    ],
                    "closeable"=> 0,
                ];
    }
}
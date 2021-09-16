<?php
/**
 * Created by PhpStorm.
 * User: martin
 * Date: 30/07/2019
 * Time: 13:05
 */

namespace Tests\Traits;


trait OpenQuestionTrait
{
    private function addOpenQuestionAndReturnQuestionId(int $testId): int
    {
        $response = $this->post(
            'api-c/test_question',
            static::getTeacherOneAuthRequestData(
                $this->getOpenQuestionAttributes(['test_id' => $testId])
            )
        );

        $response->assertStatus(200);

        return $response->decodeResponseJson()['id'];
    }

    private function getOpenQuestionAttributes(array $overrides = []): array
    {
        return array_merge([
            'question'               => '<p>Question tekst</p>\r\n',
            'answer'                 => '<p>Answer Text</p>\r\n',
            'type'                   => 'OpenQuestion',
            'score'                  => '5',
            'order'                  => 0,
            'subtype'                => 'short',
            'maintain_position'      => '0',
            'discuss'                => '1',
            'decimal_score'          => '0',
            'add_to_database'        => 1,
            'attainments'            => [],
            'note_type'              => 'NONE',
            'is_open_source_content' => 1,
            'tags'                   => [],
            'rtti'                   => null,
            'test_id'                => '9',
            "closeable"=> 0,
        ], $overrides);
    }

    private function getAttributesForOpenQuestion($testId){
        return $this->getOpenQuestionAttributes(["test_id"=> $testId]);
    }

    private function createOpenQuestionInGroup($attributes,$groupQuestionUuid){
        $url = 'api-c/group_question_question/'.$groupQuestionUuid;
        $response = $this->post(
            $url,
            static::getTeacherOneAuthRequestData(
                $attributes
            )
        );
        $response->assertStatus(200);
        $testQuestionId = $response->decodeResponseJson()['id'];
        $this->originalQuestionId = $testQuestionId;
        return $testQuestionId;
    }

    private function editOpenQuestionInGroup($uuidGroupQuestion,$uuidGroupQuestionQuestion,$attributes){
        $url = 'api-c/group_question_question/'.$uuidGroupQuestion.'/'.$uuidGroupQuestionQuestion;
        $response = $this->put(
            $url,
            static::getTeacherOneAuthRequestData(
                $attributes
            )
        );
        $response->assertStatus(200);
    }

    private function editOpenQuestion($uuidTestQuestion,$attributes){
        $url = 'api-c/test_question/'.$uuidTestQuestion;
        $response = $this->put(
            $url,
            static::getTeacherOneAuthRequestData(
                $attributes
            )
        );
        $response->assertStatus(200);
    }



}
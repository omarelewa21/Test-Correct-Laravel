<?php
/**
 * Created by PhpStorm.
 * User: martin
 * Date: 30/07/2019
 * Time: 13:05
 */

namespace tcCore\Traits\Dev;


trait RankingQuestionTrait
{
    
    private function createRankingQuestion($attributes){
        $response = $this->post(
            'api-c/test_question',
            static::getTeacherOneAuthRequestData(
                $attributes
            )
        );
        $response->assertStatus(200);
        $testQuestionId = $response->decodeResponseJson()['id'];
        $this->originalQuestionId = $testQuestionId;
        return $testQuestionId;
    }

    private function editRankingQuestion($uuid,$attributes){
        $response = $this->put(
            'api-c/test_question/'.$uuid,
            static::getTeacherOneAuthRequestData(
                $attributes
            )
        );
        $response->assertStatus(200);
    }

    private function getAttributesForRankingQuestion($testId){
        return [
            "type"=> "RankingQuestion",
            "score"=> "5",
            "question"=> "<p>GM7</p> ",
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
            "answers"=> array_merge([
                [
                    "order"=> "1",
                    "answer"=> "a"
                ],
                [
                    "order"=> "2",
                    "answer"=> "b"
                ],
                [
                    "order"=> "3",
                    "answer"=> "c"
                ]
            ],$this->getRestOfAnswerArray(3,10)),
            "tags"=> [
            ],
            "rtti"=> "R",
            "bloom"=> "Onthouden",
            "miller"=> "Weten",
            "test_id"=> $testId,
            'closeable'=> 0,
        ];
    }

}
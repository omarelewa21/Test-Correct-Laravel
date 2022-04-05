<?php
/**
 * Created by PhpStorm.
 * User: martin
 * Date: 30/07/2019
 * Time: 13:05
 */

namespace Tests\Traits;

use Ramsey\Uuid\Uuid;


trait MultipleChoiceQuestionTrait
{
    
    private function createMultipleChoiceQuestion($attributes){
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

    private function createMultipleChoiceQuestionInGroup($attributes,$groupQuestionUuid){
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

    private function editMultipleChoiceQuestion($uuid,$attributes){
        $response = $this->put(
            'api-c/test_question/'.$uuid,
            static::getTeacherOneAuthRequestData(
                $attributes
            )
        );
        $response->assertStatus(200);
    }

    private function editMultipleChoiceQuestionInGroup($uuidGroupQuestion,$uuidGroupQuestionQuestion,$attributes){
        $url = 'api-c/group_question_question/'.$uuidGroupQuestion.'/'.$uuidGroupQuestionQuestion;
        $response = $this->put(
            $url,
            static::getTeacherOneAuthRequestData(
                $attributes
            )
        );
        $response->assertStatus(200);
    }

    private function getRestOfAnswerArray($start,$end){
        $return = [];
        for ($i=$start; $i <= $end ; $i++) { 
            $return[] = [
                            "order"=> $i,
                            "answer"=> "",
                            "score"=> "0",
                        ];
        }
        return $return;
    }


    private function getAttributesForMultipleChoiceQuestion($testId){
        return [    
                    "type"=> "MultipleChoiceQuestion",
                    "score"=> "5",
                    "question"=> "<p>GM7</p> ",
                    "order"=> 0,
                    "maintain_position"=> "0",
                    "discuss"=> "1",
                    "subtype"=> "MultipleChoice",
                    "decimal_score"=> "0",
                    "add_to_database"=> 1,
                    "attainments"=> [
                    ],
                    "note_type"=> "NONE",
                    "is_open_source_content"=> 1,
                    "answers"=> array_merge([
                                [
                                    "order"=> "1",
                                    "answer"=> "a",
                                    "score"=> "5"
                                    ],
                                    [
                                    "order"=> "2",
                                    "answer"=> "b",
                                    "score"=> "0"
                                    ],
                                    [
                                    "order"=> "3",
                                    "answer"=> "c",
                                    "score"=> "0"
                                    ]
                                ],$this->getRestOfAnswerArray(3,10)),
                    "tags"=> [
                    ],
                    "rtti"=> "R",
                    "bloom"=> "Onthouden",
                    "miller"=> "Weten",
                    "test_id"=> $testId,
                    "closeable"=> 0,
                    "subject_id"=> 1,
                ];
    }

}
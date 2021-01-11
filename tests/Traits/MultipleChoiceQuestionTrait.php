<?php
/**
 * Created by PhpStorm.
 * User: martin
 * Date: 30/07/2019
 * Time: 13:05
 */

namespace Tests\Traits;


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
        $questionId = $response->decodeResponseJson()['id'];
        $this->originalQuestionId = $questionId;
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

}
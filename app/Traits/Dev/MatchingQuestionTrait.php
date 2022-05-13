<?php
/**
 * Created by PhpStorm.
 * User: martin
 * Date: 30/07/2019
 * Time: 13:05
 */

namespace tcCore\Traits\Dev;


trait MatchingQuestionTrait
{
    
    private function createMatchingQuestion($attributes){
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

    private function editMatchingQuestion($uuid,$attributes){
        $response = $this->put(
            'api-c/test_question/'.$uuid,
            static::getTeacherOneAuthRequestData(
                $attributes
            )
        );
        $response->assertStatus(200);
    }

    private function getAttributesForMatchingQuestion($testId){
        return [
            'type'=> 'MatchingQuestion',
            'score'=> '5',
            'question'=> '<p>intro</p> ',
            'order'=> 0,
            'maintain_position'=> '0',
            'discuss'=> '1',
            'subtype'=> 'Matching',
            'decimal_score'=> '0',
            'add_to_database'=> 1,
            'attainments'=> [
            ],
            'note_type'=> 'NONE',
            'is_open_source_content'=> 1,
            "answers"=> array_merge([
                [
                    'order'=> '1',
                    'left'=> 'aa',
                    'right'=> 'bb'
                ],
                [
                    'order'=> '2',
                    'left'=> 'cc',
                    'right'=> 'dd'
                ],
                [
                    'order'=> '3',
                    'left'=> 'ee',
                    'right'=> 'ff'
                ]
            ],$this->getRestOfAnswerArrayLefRight(3,49))
            ,
            'tags'=> [
            ],
            'rtti'=> null,
            'bloom'=> null,
            'miller'=> null,
            'test_id'=> $testId,
            'session_hash'=> 'FE9rzeTbhOWPs4XUyeg48Z6QgIXaaGTYnChLTRZWvY9E218GZgCYpAXezrNbYDJrzL9e437MJlksLKu9eD0591486',
            'user'=> 'd1@test-correct.nl',
            'closeable'=> 0,
        ];
    }

    private function getAttributesForCLassifyQuestion($testId){
        return [
            "type"=> "MatchingQuestion",
            "score"=> "5",
            "question"=> "<p>GM4</p> ",
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
                    "left"=> "aa",
                    "right"=> "bb\ngg"
                ],
                [
                    "order"=> "2",
                    "left"=> "cc",
                    "right"=> "dd\nhh"
                ],
                [
                    "order"=> "3",
                    "left"=> "ee",
                    "right"=> "ff\nii"
                ],
            ],$this->getRestOfAnswerArrayLefRight(4,49))
            ,
            "tags"=> [
            ],
            "rtti"=> "R",
            "bloom"=> "Onthouden",
            "miller"=> "Weten",
            "test_id"=> $testId,
        ];
    }

    private function getRestOfAnswerArrayLefRight($start,$end){
            $return = [];
            for ($i=$start; $i <= $end ; $i++) {
                $return[] = [
                    "order"=> $i,
                    "left"=> "",
                    "right"=> "",
                ];
            }
            return $return;
    }

}
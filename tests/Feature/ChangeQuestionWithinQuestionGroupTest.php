<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChangeQuestionWithinQuestionGroupTest extends TestCase
{
    use DatabaseTransactions;


    /** @test */
    public function a_teacher_can_change_the_properties_of_a_question_within_a_question_group(): void
    {
        $response = $this->get(
            static::authTeacherOneGetRequest(
                'group_question_question/20/2',
                []
            )
        );
        $response->assertStatus(200);
    }

    /** @test */
    public function a_teacher_can_change_the_order_of_the_questions_in_a_group()
    {
        $responseList = $this->get(
            static::authTeacherOneGetRequest(
                'test_question/20',
                []
            )
        );

        $list = collect($responseList->decodeResponseJson()['question']['group_question_questions'])->map(function ($question) {
            return (object)[
                'order'       => $question['order'],
                'question_id' => $question['question_id'],
            ];
        });

        $orderResponse = $this->put(
            'http://test-correct.test/group_question_question/20/2/reorder',
            static::getTeacherOneAuthRequestData([
                "order" => "1",
            ])
        );

        $orderResponse->assertStatus(200);

        $responseList2 = $this->get(
            static::authTeacherOneGetRequest(
                'test_question/20',
                []
            )
        );

        $list2 = collect($responseList2->decodeResponseJson()['question']['group_question_questions'])->map(function ($question) {
            return (object)[
                'order'       => $question['order'],
                'question_id' => $question['question_id'],
            ];
        });

        $this->assertNotEquals(
            $list, $list2
        );
        $this->assertEquals(22,
            $list2->filter(function ($item) {
                return $item->order == 1;
            })->first()->question_id
        );


    }

}

<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use tcCore\Traits\Dev\OpenQuestionTrait;

class ChangeQuestionWithinQuestionGroupTest extends TestCase
{
    use DatabaseTransactions, OpenQuestionTrait;


    /** @test */
    public function a_teacher_can_change_the_properties_of_a_question_within_a_question_group(): void
    {
        $response = $this->get(
            static::authTeacherOneGetRequest(
                'group_question_question/5/1',
                []
            )
        );
        $response->assertStatus(200);
    }

    /** @test */
    public function a_teacher_can_change_the_order_of_the_questions_in_a_group()
    {
        // first add an extra question to group nr 5;
        $this->post(
            'group_question_question/5',
            static::getTeacherOneAuthRequestData(
                $this->getOpenQuestionAttributes()
            )
        );


        // because of a bug I have to repeat this step.
        $response = $this->post(
            'group_question_question/5',
            static::getTeacherOneAuthRequestData(
                $this->getOpenQuestionAttributes()
            )
        );
        $response->assertStatus(200);
        $questionId = $response->decodeResponseJson()['question_id'];


        $responseList = $this->get(
            static::authTeacherOneGetRequest(
                'test_question/5',
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
            'group_question_question/5/4/reorder',
            static::getTeacherOneAuthRequestData([
                "order" => "1",
            ])
        );

        $orderResponse->assertStatus(200);

        $responseList2 = $this->get(
            static::authTeacherOneGetRequest(
                'test_question/5',
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
        $this->assertEquals($questionId,
            $list2->filter(function ($item) {
                return $item->order == 1;
            })->first()->question_id
        );
    }

    /** @test */
    public function a_teacher_can_delete_a_question_from_a_group()
    {

        $responseList = $this->get(
            static::authTeacherOneGetRequest(
                'test_question/5',
                []
            )
        );
        $this->assertCount(
            1,
            $responseList->decodeResponseJson()['question']['group_question_questions']
        );


        $this->delete(
            'http://test-correct.test/group_question_question/5/1',
            static::getTeacherOneAuthRequestData()
        );

        $responseList2 = $this->get(
            static::authTeacherOneGetRequest(
                'test_question/5',
                []
            )
        );

        $this->assertCount(
            0,
            $responseList2->decodeResponseJson()['question']['group_question_questions']
        );
    }


}

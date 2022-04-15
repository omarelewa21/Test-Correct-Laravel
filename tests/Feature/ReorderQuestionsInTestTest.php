<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\TestQuestion;
use tcCore\User;
use tcCore\GroupQuestionQuestion;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use tcCore\Traits\Dev\OpenQuestionTrait;
use tcCore\Traits\Dev\TestTrait;
use tcCore\Traits\Dev\GroupQuestionTrait;
use tcCore\Traits\Dev\MultipleChoiceQuestionTrait;

class ReorderQuestionsInTestTest extends TestCase
{
    use DatabaseTransactions, 
        OpenQuestionTrait, 
        TestTrait,
        GroupQuestionTrait,
        MultipleChoiceQuestionTrait;


    /** @test */
    public function a_teacher_can_reorder_questions(): void
    {
        $testId = $this->addTestAndReturnTestId();

        $questionIdOne = $this->addOpenQuestionAndReturnQuestionId($testId);
        $questionIdTwo = $this->addOpenQuestionAndReturnQuestionId($testId);

        $q1 = TestQuestion::find($questionIdOne);
        $q2 = TestQuestion::find($questionIdTwo);

        $this->assertEquals(1, $q1->order);
        $this->assertEquals(2, $q2->order);

        $reorderResponse = $this->put(
            sprintf('api-c/test_question/%s/reorder', $q2->uuid),
            static::getTeacherOneAuthRequestData([
                "order"=> "1",
            ])
        );


        $reorderResponse->assertStatus(200);

        $this->assertEquals(2, $q1->fresh()->order);
        $this->assertEquals(1, $q2->fresh()->order);
    }

    /** @test */
    public function a_teacher_can_reorder_questions_in_group(): void
    {
        $testId = $this->addTestAndReturnTestId();

        $groupTestQuestionId = $this->addQuestionGroupAndReturnId($testId);
        $groupTestQuestion = TestQuestion::find($groupTestQuestionId);
        $attributes = $this->getAttributesForMultipleChoiceQuestion($testId);
        $questionIdOne = $this->createMultipleChoiceQuestionInGroup($attributes,$groupTestQuestion->uuid);
        $questionIdTwo = $this->createMultipleChoiceQuestionInGroup($attributes,$groupTestQuestion->uuid);

        $q1 = GroupQuestionQuestion::find($questionIdOne);
        $q2 = GroupQuestionQuestion::find($questionIdTwo);

        $this->assertEquals(1, $q1->order);
        $this->assertEquals(2, $q2->order);

        $reorderResponse = $this->put(
            sprintf('api-c/group_question_question/%s/%s/reorder',$groupTestQuestion->uuid, $q2->uuid),
            static::getTeacherOneAuthRequestData([
                "order"=> "1",
            ])
        );

        $reorderResponse->assertStatus(200);

        $this->assertEquals(2, $q1->fresh()->order);
        $this->assertEquals(1, $q2->fresh()->order);
    }
}

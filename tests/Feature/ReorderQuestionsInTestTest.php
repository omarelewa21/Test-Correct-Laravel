<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\TestQuestion;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Traits\OpenQuestionTrait;
use Tests\Traits\TestTrait;

class ReorderQuestionsInTestTest extends TestCase
{
    use DatabaseTransactions, OpenQuestionTrait, TestTrait;


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
}

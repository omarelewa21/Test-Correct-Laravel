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
}

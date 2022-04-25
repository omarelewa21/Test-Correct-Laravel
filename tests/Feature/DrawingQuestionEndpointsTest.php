<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\ArchivedModel;
use tcCore\Gates\StudentGate;
use tcCore\Gates\TeacherGate;
use tcCore\Http\Helpers\SvgHelper;
use tcCore\TestTake;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Mockery\MockInterface;

class DrawingQuestionEndpointsTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function the_question_preview_image_endpoint_redirects_to_home_when_not_authenticated()
    {
        $currentUuid = '9dbd6346-f9b3-479b-ae25-758f3e1711ee';
        $svgHelper = new SvgHelper($currentUuid);
        $response = $this->get(
            route('drawing-question.background-question-svg', [
                'identifier'      => $currentUuid,
                'drawingQuestion' => $currentUuid])
        );
        $response->assertRedirect(config('api.home'));
    }

    /** @test */
    public function the_question_preview_image_endpoint_redirects_to_home_when_logged_in_as_student_but_does_not_pass_gate()
    {
        $currentUuid = '9dbd6346-f9b3-479b-ae25-758f3e1711ee';
        $svgHelper = new SvgHelper($currentUuid);
        $studentOne = User::where('username', self::USER_STUDENT_ONE)->first();
        $this->actingAs($studentOne);

        $mock = $this->mock(StudentGate::class, function (MockInterface $mock) use ($studentOne) {
            $mock->shouldReceive('canAccessDrawingQuestionBackgroundImage')
                ->with($studentOne)
                ->andReturn(false)
                ->once();
        });

        $response = $this->get(
            route('drawing-question.background-question-svg', [
                'identifier'      => $currentUuid,
                'drawingQuestion' => $currentUuid
            ])
        );

        $response->assertRedirect(route('auth.login'));
    }

    /** @test */
    public function the_question_preview_image_endpoint_returns_image_when_logged_in_as_student_and_passes_gate()
    {
        $currentUuid = '9dbd6346-f9b3-479b-ae25-758f3e1711ee';
        $svgHelper = new SvgHelper($currentUuid);
        $studentOne = User::where('username', self::USER_STUDENT_ONE)->first();
        $this->actingAs($studentOne);

        $mock = $this->mock(StudentGate::class, function (MockInterface $mock) use ($studentOne) {
            $mock->shouldReceive('canAccessDrawingQuestionBackgroundImage')
                ->with($studentOne)
                ->andReturn(true)
                ->once();
        });

        $response = $this->get(
            route('drawing-question.background-question-svg', [
                'identifier'      => $currentUuid,
                'drawingQuestion' => $currentUuid
            ])
        );
    }


    /** @test */
    public function the_question_preview_image_endpoint_redirects_to_home_when_logged_in_as_teacher_doesnt_pass_gate()
    {
        $currentUuid = '9dbd6346-f9b3-479b-ae25-758f3e1711ee';
        $svgHelper = new SvgHelper($currentUuid);
        $this->actingAs($teacherTwo = User::where('username', self::USER_TEACHER_TWO)->first());

        $mock = $this->mock(TeacherGate::class, function (MockInterface $mock) use ($teacherTwo) {
            $mock->shouldReceive('canAccessDrawingQuestionBackgroundImage')
                ->with($teacherTwo)
                ->andReturn(false)
                ->once();
        });

        $response = $this->get(
            route('drawing-question.background-question-svg', [
                'identifier'      => $currentUuid,
                'drawingQuestion' => $currentUuid
            ])
        );

        $response->assertRedirect(route('auth.login'));
    }


}

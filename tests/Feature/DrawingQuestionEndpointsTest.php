<?php

namespace Tests\Feature;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\ArchivedModel;
use tcCore\Http\Helpers\SvgHelper;
use tcCore\TestTake;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

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
    public function the_question_preview_image_endpoint_redirects_to_home_when_logged_in_as_student_but_not_()
    {
        $currentUuid = '9dbd6346-f9b3-479b-ae25-758f3e1711ee';
        $svgHelper = new SvgHelper($currentUuid);
        $this->actingAs(User::where('username', self::USER_STUDENT_ONE)->first());

        $response = $this->get(
            route('drawing-question.background-question-svg', [
                'identifier'      => $currentUuid,
                'drawingQuestion' => $currentUuid
            ])
        );
        $response->assertRedirect(route('auth.login'));


    }


}

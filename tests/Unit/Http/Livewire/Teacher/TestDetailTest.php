<?php

namespace Tests\Unit\Http\Livewire\Teacher;

use Livewire\Livewire;
use Livewire\Request;
use tcCore\Http\Livewire\Teacher\TestsOverview;
use tcCore\Test;
use Tests\TestCase;
use tcCore\Http\Helpers\QtiImporter\VersionTwoDotTwoDotZero\QtiResource;
use tcCore\QtiModels\QtiResource as Resource;

class TestDetailTest extends TestCase
{
    /** @test */
    public function as_a_guest_i_cannot_see_the_test_overview_page()
    {
        $this->get(route('teacher.test-detail', ['uuid' => Test::find(1)->uuid]))->assertStatus(302);
    }

    /** @test */
    public function as_a_student_i_cannot_see_the_test_overview_page()
    {
        $this->actingAs($this->getStudentOne());
        $this->get(route('teacher.test-detail', ['uuid' => Test::find(1)->uuid]))->assertStatus(302);
    }

    /** @test */
    public function as_a_teacher_i_can_see_test_detail_page()
    {
        $this->withoutExceptionHandling();

        $this->actingAs($this->getTeacherOne());
        $response = $this->get(route('teacher.test-detail', ['uuid' => Test::find(1)->uuid]))->assertStatus(200);
    }

}

<?php

namespace Tests\Unit\Http\Livewire\Teacher;

use Livewire\Livewire;
use Livewire\Request;
use tcCore\Http\Livewire\Teacher\TestsOverview;
use Tests\TestCase;
use tcCore\Http\Helpers\QtiImporter\VersionTwoDotTwoDotZero\QtiResource;
use tcCore\QtiModels\QtiResource as Resource;

class TestOverviewTest extends TestCase
{
    /** @test */
    public function as_a_guest_i_cannot_see_the_test_overview_page()
    {
        $this->get(route('teacher.tests'))->assertStatus(302);
    }

    /** @test */
    public function as_a_student_i_cannot_see_the_test_overview_page()
    {
        $this->actingAs($this->getStudentOne());
        $this->get(route('teacher.tests'))->assertStatus(302);
    }

    /** @test */
    public function as_a_teacher_i_can_see_test_overview_page()
    {
        $this->withoutExceptionHandling();

        $this->actingAs($this->getTeacherOne());

        $response = $this->get(route('teacher.tests'))->assertStatus(200);

        collect(['Persoonlijk', 'School', 'Nationaal', 'Examens', 'Cito Snelstart'])
            ->each(function ($tab) use ($response) {
                $response->assertSee($tab);
            });
    }
    
    /** @test */
    public function when_on_persoonlijk_tab_no_author_filter_is_present()
    {
        $this->withoutExceptionHandling();

        $this->actingAs($this->getTeacherOne());

        Livewire::test(TestsOverview::class)
            ->set('openTab', 'personal')
            ->assertDontSee('Auteurs');
    }

    /** @test */
    public function when_on_school_tab_no_author_filter_is_present()
    {
        $this->withoutExceptionHandling();

        $this->actingAs($this->getTeacherOne());

        Livewire::test(TestsOverview::class)
            ->set('openTab', 'school')
            ->assertSee('Auteurs');

    }
}

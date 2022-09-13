<?php

namespace Tests\Feature\Student\Analyses;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AnalysesTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function when_logged_in_as_student_it_should_show_the_analyses_page_for_a_student()
    {
        $this->withoutExceptionHandling();
        $this->login($this->getStudentOne());
        $this->get(route('student.analyses.show'))
            ->assertStatus(200)
            ->assertSee('Analyses');

    }
}

<?php

namespace Tests\Feature\Student\Analyses;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\ScenarioLoader;
use Tests\TestCase;

class AnalysesTest extends TestCase
{

    protected $loadScenario = FactoryScenarioSchoolSimple::class;

    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = ScenarioLoader::get('jonne');
    }

    /** @test */
    public function when_logged_in_as_student_it_should_show_the_analyses_page_for_a_student()
    {
        $this->withoutExceptionHandling();
        dd($this->user);
        $this->login($this->user);
        $this->get(route('student.analyses.show'))
            ->assertStatus(200)
            ->assertSee('Analyses');

    }
}

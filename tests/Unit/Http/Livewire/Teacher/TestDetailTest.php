<?php

namespace Tests\Unit\Http\Livewire\Teacher;

use tcCore\FactoryScenarios\FactoryScenarioSchoolSimpleWithTest;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Test;
use tcCore\User;
use Tests\ScenarioLoader;
use Tests\TestCase;

class TestDetailTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolSimpleWithTest::class;

    private User $user;

    private User $studentOne;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = ScenarioLoader::get('user');
        $this->studentOne = ScenarioLoader::get('student1');
        $this->actingAs($this->user);
        ActingAsHelper::getInstance()->setUser($this->user);
    }

    /** @test */
    public function as_a_guest_i_cannot_see_the_test_overview_page()
    {
        ActingAsHelper::getInstance()->reset();
        auth()->logout();
        $this->get(route('teacher.test-detail', ['uuid' => Test::find(1)->uuid]))->assertStatus(302);
    }

    /** @test */
    public function as_a_student_i_cannot_see_the_test_overview_page()
    {
        $this->actingAs($this->studentOne);
        $this->get(route('teacher.test-detail', ['uuid' => Test::find(1)->uuid]))->assertStatus(302);
    }

    /** @test */
    public function as_a_teacher_i_can_see_test_detail_page()
    {
        $this->withoutExceptionHandling();

        $this->actingAs($this->user);
        $this->get(route('teacher.test-detail', ['uuid' => Test::find(1)->uuid]))->assertStatus(200);
    }
}

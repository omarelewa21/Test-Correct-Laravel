<?php

namespace Tests\Unit\Http\Livewire\Teacher;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimpleWithTest;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeDiscussed;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Http\Livewire\Teacher\Assessment;
use Tests\ScenarioLoader;
use Tests\TestCase;

class AssessmentTest extends TestCase
{
    use DatabaseTransactions;

    protected $loadScenario = FactoryScenarioSchoolSimpleWithTest::class;
    protected $teacher;
    protected $studentOne;
    protected $assessmentPage;
    protected $questions;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = ScenarioLoader::get('user');
        $this->studentOne = ScenarioLoader::get('student1');
        $this->actingAs($this->teacher);
        ActingAsHelper::getInstance()->setUser($this->teacher);

        $this->testTake = FactoryScenarioTestTakeDiscussed::createTestTake(user: $this->teacher);
        $this->questions = $this->testTake->test->getFlatQuestionList();
    }

    /** @test */
    public function can_open_assessment_to_choice_page()
    {
        $this->assertNull($this->testTake->assessed_at);
        $this->assertNull($this->testTake->assessment_type);
        $this->assertNull($this->testTake->assessing_question_id);

        Livewire::test(Assessment::class, ['testTake' => $this->testTake])
            ->assertSet('headerCollapsed', false)
            ->assertSee(__('assessment.Kies je nakijkmethode'));
    }

    /** @test */
    public function can_start_assessment_from_choice_page()
    {
        $firstQuestion = $this->testTake->test->testQuestions->first()->question;

        Livewire::test(Assessment::class, ['testTake' => $this->testTake])
            ->assertSet('headerCollapsed', false)
            ->assertDontSee($firstQuestion->type_name)
            ->call('handleHeaderCollapse', ['ALL', false])
            ->assertSee($firstQuestion->type_name);
    }


    /** @test */
    public function can_navigate_to_next_question_via_header_navigation()
    {
        $firstQuestion = $this->questions->first();
        $secondQuestion = $this->questions->get(1);

        Livewire::test(Assessment::class, ['testTake' => $this->testTake])
            ->call('handleHeaderCollapse', ['ALL', false])
            ->assertSee($firstQuestion->type_name)
            ->call('loadQuestion', 2, 'incr')
            ->assertSee($secondQuestion->type_name);
    }


    /** @test */
    public function can_navigate_to_last_question_via_header_navigation()
    {
        $firstQuestion = $this->questions->first();
        $lastQuestion = $this->questions->last();

        Livewire::test(Assessment::class, ['testTake' => $this->testTake])
            ->call('handleHeaderCollapse', ['ALL', false])
            ->assertSee($firstQuestion->type_name)
            ->call('loadQuestion', $this->questions->count(), 'last')
            ->assertSee($lastQuestion->type_name);
    }

    /** @test */
    public function can_navigate_to_previous_question_via_header_navigation()
    {
        $secondQuestion = $this->questions->get(1);

        Livewire::test(Assessment::class, ['testTake' => $this->testTake])
            ->call('handleHeaderCollapse', ['ALL', false])
            ->call('loadQuestion', 3, 'incr')
            ->assertDontSee($secondQuestion->type_name)
            ->call('loadQuestion', 2, 'incr')
            ->assertSee($secondQuestion->type_name);
    }


    /** @test */
    public function can_navigate_to_first_question_via_header_navigation()
    {
        $firstQuestion = $this->questions->first();

        Livewire::test(Assessment::class, ['testTake' => $this->testTake])
            ->call('handleHeaderCollapse', ['ALL', false])
            ->call('loadQuestion', $this->questions->count(), 'last')
            ->assertDontSee($firstQuestion->type_name)
            ->call('loadQuestion', 1, 'first')
            ->assertSee($firstQuestion->type_name);
    }

    /** @test */
    public function cannot_see_student_names_when_toggle_disabled()
    {
        Livewire::test(Assessment::class, ['testTake' => $this->testTake])
            ->assertSet('assessmentContext.showStudentNames', false)
            ->call('handleHeaderCollapse', ['ALL', false])
            ->call('loadQuestion', 2, 'incr') //Load second question because first is infoscreen;
            ->assertDontSeeHtml(
                sprintf('<span class="ml-2 truncate max-w-[170px]">%s</span>', $this->studentOne->name_first)
            );
    }

    /** @test */
    public function can_see_student_names_when_toggle_enabled()
    {
        Livewire::test(Assessment::class, ['testTake' => $this->testTake])
            ->assertSet('assessmentContext.assessment_show_student_names', false)
            ->set('assessmentContext.assessment_show_student_names', true)
            ->call('handleHeaderCollapse', ['ALL', false])
            ->call('loadQuestion', 2, 'incr') //Load second question because first is infoscreen;
            ->assertSeeHtml(
                sprintf('<span class="ml-2 truncate max-w-[170px]">%s</span>', $this->studentOne->name_first)
            );
    }
}

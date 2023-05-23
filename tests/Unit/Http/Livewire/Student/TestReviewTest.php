<?php

namespace Tests\Unit\Http\Livewire\Student;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimpleWithTest;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeDiscussed;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Http\Livewire\Student\TestReview;
use tcCore\Http\Livewire\Teacher\UploadTest;
use Tests\ScenarioLoader;
use Tests\TestCase;

class TestReviewTest extends TestCase
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
        $this->testTake->show_results = Carbon::now()->addDay();
        $this->testTake->save();

        $this->questions = $this->testTake->test->getFlatQuestionList();
    }

    /** @test */
    public function can_start_review()
    {
        $this->actingAs($this->studentOne)
            ->get(route('student.test-review', ['testTakeUuid' => $this->testTake->uuid]))
            ->assertSuccessful()
            ->assertSeeLivewire(TestReview::class);

        Livewire::test(TestReview::class, ['testTakeUuid' => $this->testTake->uuid])
            ->assertSee($this->questions->first()->type_name);
    }

    /** @test */
    public function can_navigate_questions()
    {
        $this->actingAs($this->studentOne);
        Livewire::test(TestReview::class, ['testTakeUuid' => $this->testTake->uuid])
            ->assertSeeHtml(
                sprintf('<span class="align-middle cursor-default">%s</span>', 1)
            )
            ->call('loadQuestion', 12)
            ->assertSeeHtml(
                sprintf('<span class="align-middle cursor-default">%s</span>', $this->questions->count())
            );
    }
}

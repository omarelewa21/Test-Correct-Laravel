<?php

namespace Tests\Unit\Http\Livewire\Teacher\TestTake;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimpleWithTest;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeDiscussed;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeTaken;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Http\Livewire\Teacher\TestTake\Taken;
use tcCore\TestTakeStatus;
use Tests\ScenarioLoader;
use Tests\TestCase;

class TakenTest extends TestCase
{
    use DatabaseTransactions;

    protected $loadScenario = FactoryScenarioSchoolSimpleWithTest::class;
    protected $teacher;
    protected $studentOne;
    protected $testTake;
    protected $schoolLocation;

    protected function setUp(): void
    {
        parent::setUp();

        $this->teacher = ScenarioLoader::get('user');
        $this->studentOne = ScenarioLoader::get('student1');
        $this->actingAs($this->teacher);
        ActingAsHelper::getInstance()->setUser($this->teacher);

        $this->schoolLocation = ScenarioLoader::get('school_locations')->first();
        $this->schoolLocation->allow_new_test_take_detail_page = true;
        $this->schoolLocation->save();
    }

    /** @test */
    public function can_see_waiting_room_by_default_when_status_is_taken()
    {
        $this->testTake = FactoryScenarioTestTakeTaken::createTestTake($this->teacher);
        $this->followingRedirects()
            ->get(route('teacher.test-take.open-detail', $this->testTake->uuid))
            ->assertSeeLivewire(Taken::class);

        Livewire::test(Taken::class, ['testTake' => $this->testTake])
            ->assertSee(__('test-take.Wachtkamer'));
    }

    /** @test */
    public function cannot_see_results_panes_when_status_is_taken()
    {
        $this->testTake = FactoryScenarioTestTakeTaken::createTestTake($this->teacher);

        Livewire::test(Taken::class, ['testTake' => $this->testTake])
            ->assertDontSee(__('test-take.Resultaten overzicht'))
            ->assertDontSee(__('test-take.Leerdoel analyse'));
    }

    /** @test */
    public function cannot_waiting_room_by_default_when_status_is_greater_than_taken_but_can_when_starting_co_learning()
    {
        $this->testTake = FactoryScenarioTestTakeDiscussed::createTestTake($this->teacher);

        Livewire::test(Taken::class, ['testTake' => $this->testTake])
            ->assertDontSee(__('test-take.Wachtkamer'))
            ->call('startCoLearning')
            ->assertSee(__('test-take.Wachtkamer'));
    }

    /** @test */
    public function cannot_see_standardize_pane_when_assessment_is_not_done()
    {
        $this->testTake = FactoryScenarioTestTakeDiscussed::createTestTake($this->teacher);

        Livewire::test(Taken::class, ['testTake' => $this->testTake])
            ->assertSet('assessmentDone', false)
            ->assertDontSee(__('test-take.Resultaten instellen'));
    }

    /** @test */
    public function can_see_standardize_pane_when_assessment_is_done()
    {
        $this->testTake = FactoryScenarioTestTakeDiscussed::createTestTake($this->teacher);

        Livewire::test(Taken::class, ['testTake' => $this->testTake])
            ->assertSet('assessmentDone', false)
            ->assertSet('testTakeStatusId', TestTakeStatus::STATUS_DISCUSSED)
            ->set('gradingStandard', 'n_term')
            ->set('assessmentDone', true)
            ->assertSee(__('test-take.Resultaten instellen'));
    }

    /** @test */
    public function can_show_warning_when_scoring_is_adjusted()
    {
        $this->testTake = FactoryScenarioTestTakeDiscussed::createTestTake($this->teacher);

        Livewire::test(Taken::class, ['testTake' => $this->testTake])
            ->assertSet('participantGradesChanged', false)
            ->set('gradingStandard', 'n_term')
            ->set('assessmentDone', true)
            ->set('participantGradesChanged', false)
            ->set('participantResults.0.rating', 5)
            ->assertSet('participantGradesChanged', true);
    }

    /** @test */
    public function can_only_save_rating_when_published()
    {
        $this->testTake = FactoryScenarioTestTakeDiscussed::createTestTake($this->teacher);

        $firstParticipant = $this->testTake->testParticipants()->first();
        $tpRating = $firstParticipant->rating;
        $newRating = 5;
        $this->assertNotEquals($tpRating, $newRating);

        $component = Livewire::test(Taken::class, ['testTake' => $this->testTake]);
        $component->set('gradingStandard', 'n_term')
            ->set('assessmentDone', true)
            ->set('participantResults.0.rating', $newRating);

        $this->assertEquals($tpRating, $firstParticipant->fresh()->rating);

        $component->call('publishResults');

        $this->assertEquals($firstParticipant->fresh()->rating, $newRating);
    }
}

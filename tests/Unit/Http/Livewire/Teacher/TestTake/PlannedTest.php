<?php

namespace Tests\Unit\Http\Livewire\Teacher\TestTake;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Livewire\Livewire;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimpleWithTest;
use tcCore\FactoryScenarios\FactoryScenarioTestTakePlanned;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Http\Livewire\Teacher\TestTake\Planned;
use Tests\ScenarioLoader;
use Tests\TestCase;

class PlannedTest extends TestCase
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

        $this->testTake = FactoryScenarioTestTakePlanned::createTestTake($this->teacher);
    }

    /** @test */
    public function can_not_open_detail_page_when_school_location_is_not_allowed()
    {
        $this->schoolLocation->allow_new_test_take_detail_page = false;
        $this->schoolLocation->save();
        $this->assertFalse($this->schoolLocation->allow_new_test_take_detail_page);

        $response = $this->get(route('teacher.test-take.open-detail', $this->testTake->uuid));
        $response->assertRedirect();
        $this->followRedirects($response)
            ->assertDontSeeLivewire(Planned::class);
    }

    /** @test */
    public function can_open_detail_page_when_allowed()
    {
        $this->assertTrue($this->schoolLocation->allow_new_test_take_detail_page);
        $this->followingRedirects()
            ->get(route('teacher.test-take.open-detail', $this->testTake->uuid))
            ->assertSeeLivewire(Planned::class);
    }

    /** @test */
    public function can_not_start_test_take_when_start_date_is_not_today()
    {
        $this->testTake->time_start = Carbon::tomorrow();
        $this->testTake->save();

        Livewire::test(Planned::class, ['testTake' => $this->testTake])
            ->assertSee(__('test-take.toetsafname is niet vandaag gepland'))
            ->call('startTake')
            ->assertHasErrors('cannot_start_take_before_start_date');
    }

    /** @test */
    public function can_remove_test_participants()
    {
        $tpUuid = $this->testTake->testParticipants->first()->uuid;
        $participantCount = $this->testTake->testParticipants()->count();
        Livewire::test(Planned::class, ['testTake' => $this->testTake])
            ->call('removeParticipant', $tpUuid);

        $newParticipantCount = $this->testTake->testParticipants()->count();

        $this->assertGreaterThan($newParticipantCount, $participantCount);
    }
}

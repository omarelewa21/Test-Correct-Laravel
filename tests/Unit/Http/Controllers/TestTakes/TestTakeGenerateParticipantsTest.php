<?php

namespace Tests\Unit\Http\Controllers\TestTakes;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Factories\FactoryTestTake;
use tcCore\FactoryScenarios\FactoryScenarioTestTakePlanned;
use tcCore\TestParticipant;
use tests\TestCase;

class TestTakeGenerateParticipantsTest extends TestCase
{
    use DatabaseTransactions;

    protected FactoryTestTake $testTakeFactory;
    const STUDENTS_COUNT = 5;

    protected function setUp(): void
    {
        parent::setUp();

        $this->testTakeFactory = FactoryScenarioTestTakePlanned::create($this->getTeacherOne())->testTakeFactory;
    }

    /**
     * Test that the factory's generateMany method correctly creates TestParticipants for a TestTake.
     *
     * @return void
     */
    public function test_factory_generateMany()
    {
        $testTake = $this->testTakeFactory->testTake;
        // Assert
        $this->assertEquals(self::STUDENTS_COUNT, $testTake->testParticipants()->count());
        $this->assertEquals(1, $testTake->invigilators()->count());
        foreach ($testTake->testParticipants as $participant) {
            $this->assertNotNull($participant->school_class_id);
            $this->assertNotNull($participant->test_take_status_id);
            $this->assertNotNull($participant->user_id);
            $this->assertNotNull($participant->created_at);
            $this->assertNotNull($participant->updated_at);
            $this->assertInstanceOf(TestParticipant::class, $participant);
        }
    }
}

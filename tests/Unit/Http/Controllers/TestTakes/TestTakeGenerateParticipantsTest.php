<?php

namespace Tests\Unit\Http\Controllers\TestTakes;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use tcCore\Factories\FactoryTestTake;
use tcCore\FactoryScenarios\FactoryScenarioTestTakePlanned;
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
     * A basic unit test example.
     *
     * @return void
     */
    public function test_factory_generateMany()
    {
        $testTake = $this->testTakeFactory->testTake;
        $this->assertEquals(self::STUDENTS_COUNT, $testTake->testParticipants()->count());
        foreach($testTake->testParticipants as $participant){
            $this->assertEquals(1, $participant->school_class_id);
            $this->assertEquals(1, $participant->test_take_status_id);
        }
    }
}

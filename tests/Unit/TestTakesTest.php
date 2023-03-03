<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 12/04/2019
 * Time: 13:18
 */

namespace Tests\Unit;

use tcCore\Factories\FactoryTest;
use tcCore\Factories\FactoryTestTake;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\SchoolClass;
use tcCore\Test;
use tcCore\TestKind;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\TestTakeStatus;
use Tests\ScenarioLoader;
use Tests\TestCase;

class TestTakesTest extends TestCase
{
    protected $loadScenario = FactoryScenarioSchoolSimple::class;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /** @test */
    public function a_test_takes_has_a_archived_attribute()
    {
        $teacherOne = ScenarioLoader::get('teacher1');
        $this->actingAs($teacherOne);

        $testTake = FactoryTestTake::create(
            FactoryTest::create($teacherOne)->getTestModel()
        )->testTake;

        $this->assertFalse($testTake->archived);

        $testTake->archiveForUser($teacherOne);

// reload the testTake from database; // refresh and fresh don't apply global scope why?
        $archivedTestTake = TestTake::find($testTake->getKey());

        $this->assertTrue($archivedTestTake->archived);
    }

    /** @test */
    public function it_should_return_all_test_takes_with_type_assignment()
    {
        $this->assertCount(0, TestTake::typeAssignment()->get());

        $test = $this->getTest(['test_kind_id' => TestKind::ASSIGNMENT_TYPE]);

        FactoryTestTake::create($test, ScenarioLoader::get('teacher1'));


        $list = TestTake::typeAssignment()->get();

        $this->assertCount(1, $list);

        $list->each(function ($testTake) {
            $this->assertEquals(TestKind::ASSIGNMENT_TYPE, $testTake->test->test_kind_id);
        });
    }

    /** @test */
    public function it_should_return_all_test_takes_with_status_planned()
    {
        $test = $this->getTest();
        FactoryTestTake::create($test, ScenarioLoader::get('teacher1'))->setStatusTaken();

        $this->assertCount(0, TestTake::statusPlanned()->get());

        $testTake = TestTake::first();
        $testTake->test_take_status_id = TestTakeStatus::STATUS_PLANNED;
        $testTake->save();


        $this->assertCount(1, TestTake::statusPlanned()->get());
    }

//    /** @test */
// Methods seem to be not implemented;
    public function it_should_return_all_test_takes_with_start_time_expired()
    {
        $testTake = FactoryTestTake::create($this->getTest(), ScenarioLoader::get('teacher1'))
            ->setProperties(['time_start' => now()->addMinutes(10)])
            ->testTake;


        $this->assertCount(0, TestTake::timeStartExpired()->get());

        TestTake::whereNotNull('id')
            ->update([
                'time_start' => now()->subMinutes(10),
                'time_end'   => now()->addMinutes(10)
            ]);

        dd(TestTake::get('time_start')->toArray());
        $this->assertCount(21, TestTake::timeStartExpired()->get());
    }

    //    /** @test */
// Methods seem to be not implemented;/** @test */
    public function it_should_return_all_test_takes_with_end_time_expired()
    {
        TestTake::whereNotNull('id')->update(['time_end' => now()->addMinutes(10)]);

        $this->assertCount(0, TestTake::timeEndExpired()->get());

        TestTake::whereNotNull('id')
            ->update([
                'time_start' => now()->subMinutes(60),
                'time_end'   => now()->subMinutes(10)
            ]);
        $this->assertCount(21, TestTake::timeStartExpired()->get());
    }

    /** @test */
    public function it_should_return_same_school_classes_before_and_after_refactoring()
    {
        FactoryTestTake::create($this->getTest(), ScenarioLoader::get('teacher1'));
        $testTake = ScenarioLoader::get('teacher1')->testTakes->last();

        $id = $testTake->getKey();
        $old = SchoolClass::withTrashed()->select()->whereIn('id', function ($query) use ($id) {
            $query->select('school_class_id')
                ->from(with(new TestParticipant())->getTable())
                ->where('test_take_id', $id)
                ->where('deleted_at', null);
        });

        $new = $testTake->schoolClasses();

        $this->assertEquals($old->get(), $new->get());
    }

    /** @test */
    public function it_should_be_possible_to_get_school_classes_from_multiple_test_takes()
    {
        $test = $this->getTest();
        FactoryTestTake::createWithParticipants($test);
        FactoryTestTake::createWithParticipants($test);

        $testTakeIds = ScenarioLoader::get('teacher1')->testTakes->pluck('id');

        $schoolClasses = TestTake::schoolClassesForMultiple($testTakeIds)->pluck('name');

        $this->assertNotEmpty($schoolClasses);
    }

    /**
     * @param array $properties
     * @return Test
     * @throws \Exception
     */
    private function getTest(array $properties = []): Test
    {
        return FactoryTest::create(
            ScenarioLoader::get('teacher1')
        )
            ->setProperties($properties)
            ->getTestModel();
    }


}

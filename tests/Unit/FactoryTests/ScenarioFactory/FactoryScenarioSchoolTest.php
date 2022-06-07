<?php

namespace Tests\Unit\FactoryTests\ScenarioFactory;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\Concerns\InteractsWithExceptionHandling;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use PHPUnit\Util\RegularExpression;
use tcCore\Factories\FactoryBaseSubject;
use tcCore\Factories\FactorySchool;
use tcCore\Factories\FactorySchoolClass;
use tcCore\Factories\FactorySchoolLocation;
use tcCore\Factories\FactorySchoolYear;
use tcCore\Factories\FactorySection;
use tcCore\Factories\FactoryTest;
use tcCore\FactoryScenarios\FactoryScenarioSchool001;
use tcCore\FactoryScenarios\FactoryScenarioSchoolRandomComplex;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\FactoryScenarios\FactoryScenarioTestTakeAllStatuses;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\Test;
use tcCore\User;
use Tests\TestCase;
use Tests\Unit\FactoryTests\SchoolFactory\BaseSubject;
use Tests\Unit\FactoryTests\SchoolFactory\Factory;
use Tests\Unit\FactoryTests\SchoolFactory\Period;
use Tests\Unit\FactoryTests\SchoolFactory\SchoolYear;
use Tests\Unit\FactoryTests\SchoolFactory\Subject;
use Tests\Unit\FactoryTests\SchoolFactory\Teacher;

class FactoryScenarioSchoolTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;

    public function Scenario_School_001()
    {
        //todo needed:
        //  1 school met:
        //      2 school locations
        //  1 school_location (zonder school? in test database hebben alle school_locations een School.
        //                         in geval dat school nodig is, 2de school maken voor losse school_location)
        //  5 teachers:
        //      1 in beide school_locations
        //      3 verdeeld over een van de twee school_locations (2 in school_location 1, 1 in school_location 2)
        //      1 in losse 'verkeerde' school_location (behoort niet tot school/scholengemeenschap van 1 en 2)
        // .
        //  10 Subjects:                    BaseSubjects:       Sectie:
        //      Chinees                     =>  Chinees         =>  Chinees
        //      Chinese leesvaardigheid     =>  Chinees         =>  Chinees
        //      Chinese tekstverwerking     =>  Chinees         =>  Chinees
        //      Russisch                    =>  Russisch        =>  Russisch
        //      Russische literatuur        =>  Russisch        =>  Russisch
        //      Klassiek Turks              =>  Turks           =>  Turks
        //      Modern Turks                =>  Turks           =>  Turks
        //      Italiaanse literatuur       =>  Italiaans       =>  Italiaans
        //      Spaans                      =>  Spaans          =>  Spaans
        //      Kunstzinnige vormen         =>  Kunst           =>  Kunst

    }

    /** @test */
    public function can_create_school_scenario_based_on_supplied_written_testScenario()
    {
        $factoryScenarioSchool = FactoryScenarioSchool001::create();

        $this->assertEquals(15, $factoryScenarioSchool->getStudents()->count());

        $factoryScenarioSchool->schools->each(function ($school) {
            $school->schoolLocations->each(function ($schoolLocation) {
                $this->assertGreaterThan(0, $schoolLocation->schoolLocationSections->count());
                $this->assertGreaterThan(0, $schoolLocation->educationLevels->count());
                $this->assertGreaterThan(0, $schoolLocation->schoolYears->count());
                $this->assertGreaterThan(0, $schoolLocation->schoolClasses->count());
                $schoolLocation->schoolYears->each(function ($schoolYear) {
                    $this->assertGreaterThan(0, $schoolYear->periods->count());
                });
                $schoolLocation->schoolClasses->each(function ($schoolClass) {
                    $this->assertGreaterThan(0, $schoolClass->teacher->count());
                    $this->assertGreaterThan(0, $schoolClass->students->count());
                });
            });
        });


    }

    /** @test */
    public function can_create_tests_for_all_teachers_and_their_subjects()
    {
        $startCountTests = Test::count();
        $factoryScenarioSchool = FactoryScenarioSchool001::create();

        //Assert that the Tests for SchoolYears/Periods in the past have been made correctly
        $countTestsNotCurrentYear = 0;
        Test::orderByDesc('id')
            ->limit((Test::count() - $startCountTests))
            ->get()
            ->each(function ($test) use (&$countTestsNotCurrentYear) {
                $countTestsNotCurrentYear += ($test->period->schoolyear->year == now()->format('Y') ? 0 : 1);
            });
        $this->assertEquals(2, $countTestsNotCurrentYear);
        $this->assertEquals($startCountTests + 10/*demoToetsen*/ + 12, Test::count());
    }

    /** @test */
    public function can_create_scenario_001_mulitple_times_with_an_incrementing_Letter_at_the_end_of_the_name()
    {
        $schoolScenario1schools = FactoryScenarioSchool001::create()->schools;
        $schoolScenario2schools = FactoryScenarioSchool001::create()->schools;

        $this->assertNotEquals($schoolScenario1schools->first()->name, $schoolScenario2schools->first()->name);

        $school1Letter = substr($schoolScenario1schools->first()->name,-1,1);
        $school2Letter = substr($schoolScenario2schools->first()->name,-1,1);
        $this->assertNotEquals($school1Letter, $school2Letter);
//        $this->assertEquals(++$school1Letter, $school2Letter); //++$letter ($letter=='A') == 'B'
    }

    /**
     * @test
     * create a school scenario
     */
    public function can_create_school_scenario_with_random_default_values()
    {
        $startCounts = [
            'schoolManager'  => User::count(),
            'school'         => School::count(),
            'schoolLocation' => SchoolLocation::count(),
        ];

        $factoryScenarioSchool = FactoryScenarioSchoolRandomComplex::create();

        $school = $factoryScenarioSchool->schools->first();

        $this->assertGreaterThan($startCounts['schoolManager'], User::count());
        $this->assertGreaterThan($startCounts['school'], School::count());
        $this->assertGreaterThan($startCounts['schoolLocation'], SchoolLocation::count());

        $school->schoolLocations->each(function ($schoolLocation) {
            $this->assertGreaterThan(0, $schoolLocation->schoolLocationSections->count());
            $this->assertGreaterThan(0, $schoolLocation->educationLevels->count());
            $this->assertGreaterThan(0, $schoolLocation->schoolYears->count());
            $this->assertGreaterThan(0, $schoolLocation->schoolClasses->count());
            $schoolLocation->schoolYears->each(function ($schoolYear) {
                $this->assertGreaterThan(0, $schoolYear->periods->count());
            });
            $schoolLocation->schoolClasses->each(function ($schoolClass) {
                $this->assertGreaterThan(0, $schoolClass->teacher->count());
                $this->assertGreaterThan(0, $schoolClass->students->count());
            });
        });

    }

    /** @test */
    public function can_create_simple_school_scenario()
    {
        $startCounts = [
            'schoolManager'  => User::count(),
            'school'         => School::count(),
            'schoolLocation' => SchoolLocation::count(),
        ];

        $factoryScenarioSchool = FactoryScenarioSchoolSimple::create();

        $school = $factoryScenarioSchool->schools->first();

        $this->assertGreaterThan($startCounts['schoolManager'], User::count());
        $this->assertGreaterThan($startCounts['school'], School::count());
        $this->assertGreaterThan($startCounts['schoolLocation'], SchoolLocation::count());

        $school->schoolLocations->each(function ($schoolLocation) {
            $this->assertGreaterThan(0, $schoolLocation->schoolLocationSections->count());
            $this->assertGreaterThan(0, $schoolLocation->educationLevels->count());
            $this->assertGreaterThan(0, $schoolLocation->schoolYears->count());
            $this->assertGreaterThan(0, $schoolLocation->schoolClasses->count());
            $schoolLocation->schoolYears->each(function ($schoolYear) {
                $this->assertGreaterThan(0, $schoolYear->periods->count());
            });
            $schoolLocation->schoolClasses->each(function ($schoolClass) {
                $this->assertGreaterThan(0, $schoolClass->teacher->count());
                $this->assertGreaterThan(0, $schoolClass->students->count());
            });
        });
    }

    /** @test */
    public function can_get_teachers_from_school_scenario()
    {
        $factoryScenarioSchool = FactoryScenarioSchoolRandomComplex::create();

        $teachers = $factoryScenarioSchool->getTeachers();

        $teachers->each(function ($teacher) {
            $this->assertInstanceOf('tcCore\User', $teacher);
            $this->assertEquals(1, $teacher->roles()->first()->id);
        });
    }

    /** @test */
    public function can_create_test_for_school_scenario()
    {
        $schoolScenarioFactory = FactoryScenarioSchoolRandomComplex::create();
        $teacher = $schoolScenarioFactory->getTeachers()->first();

        $startCount = Test::count();

        $test = FactoryTest::create($teacher)->getTestModel();

        $this->assertEquals(++$startCount, Test::count());
        $this->assertEquals($teacher->schoolLocation->name, $test->owner->name);
        //dd($test->period->exists(), $test->educationLevel, $school->schoolLocations->first()->educationLevels);

    }
}
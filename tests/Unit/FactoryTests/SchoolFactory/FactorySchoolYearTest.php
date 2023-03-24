<?php

namespace Tests\Unit\FactoryTests\SchoolFactory;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use tcCore\Factories\FactorySchool;
use tcCore\Factories\FactorySchoolLocation;
use tcCore\Factories\FactorySchoolYear;
use tcCore\Period;
use tcCore\SchoolYear;
use Tests\TestCase;

class FactorySchoolYearTest extends TestCase
{
    use WithFaker;

    /** @test */
    public function can_create_school_year_for_a_school_location()
    {
//todo implement:   \tcCore\Http\Helpers\GlobalStateHelper::getInstance()->setPreventDemoEnvironmentCreationForSchoolLocation(true);

        Carbon::setTestNow(Carbon::create(2022,5,2));

        $schoolLocation = FactorySchoolLocation::create(
            FactorySchool::create(
                'TestSchool'
            )->school
        )->schoolLocation;
        $startCount = SchoolYear::count();

        $schoolYearFactory = FactorySchoolYear::create($schoolLocation, 2022);

        $this->assertEquals(2022, $schoolYearFactory->schoolYear->year);
        $this->assertEquals($startCount + 1, SchoolYear::count()); //there are 2 created (now? not in the past?)
    }
    /** @test */
    public function can_create_school_year_only_if_no_current_school_year_exist()
    {
        Carbon::setTestNow(Carbon::create(2022, 9, 1)); //default created schoolyear starts at current year, month 8.

        $startCount = SchoolYear::count();
        $schoolLocation = FactorySchoolLocation::create(
            FactorySchool::create(
                'TestSchool'
            )->school
        )->schoolLocation;

        $schoolYearFactory = FactorySchoolYear::create($schoolLocation, 2022, true)
            ->addPeriodFullYear();

        $this->assertEquals(2022, $schoolYearFactory->schoolYear->year);
        $this->assertEquals($startCount + 1, SchoolYear::count());
    }

    /** @test */
    public function can_create_Period_for_a_school_year()
    {
        $schoolLocation = FactorySchoolLocation::create(
            FactorySchool::create(
                'TestSchool'
            )->school
        )->schoolLocation;
        $startCount = Period::count();

        $schoolYearFactory = FactorySchoolYear::create($schoolLocation, 2022, true);

        $startDate = Carbon::today()->startOfYear();

        $schoolYearFactory
            ->addPeriod('Period A', $startDate->toDateString(), $startDate->addMonths(6)->subDay())
            ->addPeriod('Period B', $startDate->addDay()->toDateString(), $startDate->endOfYear());

        $this->assertEquals($startCount + 2, Period::count());
    }

    /** @test */
    public function can_add_period_for_a_full_year_to_school_year()
    {
        $schoolLocation = FactorySchoolLocation::create(
            FactorySchool::create(
                'TestSchool'
            )->school
        )->schoolLocation;
        $startCount = Period::count();
        $schoolYearFactory = FactorySchoolYear::create($schoolLocation, 2022);

        $schoolYearFactory->addPeriodFullYear('PeriodA');

        $this->assertEquals($startCount + 1, Period::count());
        $this->assertEquals('PeriodA', $schoolLocation->schoolYears()->orderByDesc('id')->first()->periods()->first()->name);
    }

    /** @test */
    public function can_add_four_quarter_periods_to_a_school_years()
    {
        $schoolLocation = FactorySchoolLocation::create(
            FactorySchool::create(
                'TestSchool'
            )->school
        )->schoolLocation;
        $startCount = Period::count();
        $schoolYearFactory = FactorySchoolYear::create($schoolLocation, 2022);

        $periodNames = [
            'PeriodA',
            'PeriodB',
            'PeriodC',
            'PeriodD',
        ];
        $schoolYearFactory->addFourQuarterYearPeriods($periodNames);

        $this->assertEquals($startCount + 4, Period::count());
        $this->assertEquals(
            $periodNames,
            $schoolYearFactory
                ->schoolYear
                ->periods
                ->map(function ($period) {
                    return $period->name;
                })->toArray()
        );
    }
}
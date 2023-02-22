<?php

namespace tcCore\Factories;

use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use tcCore\Factories\Traits\DoWhileLoggedInTrait;
use tcCore\Factories\Traits\RandomCharactersGeneratable;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\Period;
use tcCore\SchoolLocation;
use tcCore\SchoolYear;

class FactorySchoolYear
{
    use RandomCharactersGeneratable;
    use DoWhileLoggedInTrait;

    public SchoolYear $schoolYear;
    protected int $year;

    public static function create(SchoolLocation $schoolLocation, int $year, $doNotCreateIfCurrentSchoolYearExists = false)
    {
        $factory = new static;
        ActingAsHelper::getInstance()->setUser($schoolLocation->users->first());
        $factory->year = $year;

        if ($doNotCreateIfCurrentSchoolYearExists && SchoolYearRepository::getCurrentSchoolYear()) {
            $factory->schoolYear = SchoolYearRepository::getCurrentSchoolYear();
        }
        if (!isset($factory->schoolYear) || is_null($factory->schoolYear)) {
            $factory->schoolYear = new SchoolYear(['year' => $factory->year]);

            $schoolLocation->schoolYears()->save($factory->schoolYear);
        }

        return $factory;
    }

    public function addPeriod(string $periodName, $periodStartDate, $periodEndDate)
    {
        $period = new Period([
            'name'       => $periodName,
            'start_date' => $periodStartDate,
            'end_date'   => $periodEndDate,
        ]);

        // note: $this->schoolYear->schoolLocations()->first()->users->first()
        //  uses one of the demo users to create a period, because Period checks for ($user->schoolLocation !== null)
        //  account manager won't work, because this user is not a member of the school.

        $this->doWhileLoggedIn(function () use ($period) {
            $this->schoolYear->periods()->save($period);
        }, $this->schoolYear->schoolLocations()->first()->users->first());


        return $this;
    }

    public function addPeriodFullYear(string $periodName = 'FullYearPeriod')
    {
        $year = $this->year;

        $period = new Period([
            'name'       => $periodName,
            'start_date' => Carbon::create($year)->startOfYear(),
            'end_date'   => Carbon::create($year)->endOfYear(),
        ]);

        $this->doWhileLoggedIn(function () use ($period) {
            $this->schoolYear->periods()->save($period);
        }, $this->schoolYear->schoolLocations()->first()->users->first());

        return $this;
    }

    public function addFourQuarterYearPeriods(array $periodNames = ['Q1', 'Q2', 'Q3', 'Q4'])
    {
        if (count($periodNames) !== 4) {
            throw new \Exception('please supply precisely four period names, one for each quarter of the year.');
        }

        $year = $this->year;

        $date = Carbon::create($year)->startOfYear();

        foreach ($periodNames as $periodName) {

            $period = new Period([
                'name'       => $periodName,
                'start_date' => $date->startOfQuarter()->toDateString(),
                'end_date'   => $date->endOfQuarter()->toDateString(),
            ]);
            $date->addDay();

            $this->doWhileLoggedIn(function () use ($period) {
                $this->schoolYear->periods()->save($period);
            }, $this->schoolYear->schoolLocations()->first()->users->first());
        }


        return $this;
    }
}
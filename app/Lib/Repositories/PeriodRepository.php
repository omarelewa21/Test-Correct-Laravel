<?php namespace tcCore\Lib\Repositories;

use Carbon\Carbon;
use tcCore\Period;
use tcCore\SchoolLocation;

class PeriodRepository
{

    private static $currentPeriod = null;
    private static $currentPeriods = null;
    private static $previousPeriod = null;
    private static $periodsOfCurrentOrPreviousSchoolYear = null;

    public static function getCurrentPeriod()
    {
        if (static::$currentPeriod === null) {
            $now = Carbon::now();
            static::$currentPeriod = Period::filtered()->where('start_date', '<=', $now->toDateString())->where('end_date', '>=',
                $now->toDateString())->first();
        }
        return static::$currentPeriod;

    }

    public static function getCurrentPeriods()
    {
        if (static::$currentPeriods === null) {
            static::$currentPeriods = Period::filtered()
                ->where('start_date', '<=', now())
                ->where('end_date', '>=', now())
                ->get();
        }
        return static::$currentPeriods;
    }

    public static function reset()
    {
        static::$currentPeriod = null;
        static::$currentPeriods = null;
    }

    public static function getCurrentOrPreviousPeriod()
    {
        $now = Carbon::now();
        $result = Period::filtered()->where('start_date', '<=', $now->toDateString())->where('end_date', '>=',
            $now->toDateString())->first();

        if ($result === null) {
            $maxDate = Period::filtered()->max('end_date');
            $result = Period::filtered()->where('start_date', '<=', $maxDate)->where('end_date', '>=',
                $maxDate)->first();
        }

        return $result;
    }

    public static function getPreviousPeriod()
    {
        if (static::$previousPeriod === null) {

            $current = self::getCurrentPeriod();
            $maxDate = Period::filtered()->max('end_date');
            static::$previousPeriod = Period::filtered()
                ->where('start_date', '<=', $maxDate)
                ->where('end_date', '>=', $maxDate)
                ->where('id', '<>', $current->getKey())
                ->first();
        }

        return static::$previousPeriod;
    }

    public static function getPeriodsOfCurrentOrPreviousSchoolYear()
    {
        if (static::$periodsOfCurrentOrPreviousSchoolYear === null) {
            $period = static::getCurrentOrPreviousPeriod();
            static::$periodsOfCurrentOrPreviousSchoolYear = Period::filtered()
                ->where('school_year_id', $period->getAttribute('school_year_id'))
                ->get();
        }

        return static::$periodsOfCurrentOrPreviousSchoolYear;
    }

    public static function getCurrentPeriodForSchoolLocation($schoolLocation, $logger = false, $throwException = true)
    {
        $schoolYears = $schoolLocation->schoolLocationSchoolYears->map(function ($l) {
            return $l->school_year_id;
        });

        $periods = Period::where('start_date', '<=', Carbon::today())
            ->where('end_date', '>=', Carbon::today())
            ->whereIn('school_year_id', $schoolYears->toArray())
            ->get();

        if (!$periods->count()) {
            $msg = sprintf(
                'No valid period found for school location %s with id %d.',
                $schoolLocation->name,
                $schoolLocation->id
            );
            if ($logger) {
                $logger->addToLog($msg);
            }

            if (!$throwException) {
                return false;
            }

            throw new \Exception($msg);

        }
        return $periods->first();
    }

}

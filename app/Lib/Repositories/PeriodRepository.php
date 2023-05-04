<?php namespace tcCore\Lib\Repositories;

use Carbon\Carbon;
use tcCore\Period;
use tcCore\SchoolLocation;

class PeriodRepository
{
    public static function getCurrentPeriod()
    {
        $now = Carbon::now();
        $result = Period::filtered()->where('start_date', '<=', $now->toDateString())->where('end_date', '>=',
            $now->toDateString())->first();
         return $result;
    }

    public static function getCurrentPeriods()
    {
        return Period::filtered()->where('start_date', '<=', now())
            ->where('end_date', '>=', now())->get();
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
        $current = self::getCurrentPeriod();
        $maxDate = Period::filtered()->max('end_date');
        return Period::filtered()
            ->where('start_date', '<=', $maxDate)
            ->where('end_date', '>=', $maxDate)
            ->where('id','<>',$current->getKey())
            ->first();
    }

    public static function getPeriodsOfCurrentOrPreviousSchoolYear()
    {
        $period = static::getCurrentOrPreviousPeriod();

        $results = Period::filtered()->where('school_year_id', $period->getAttribute('school_year_id'))->get();

        return $results;
    }

    public static function getCurrentPeriodForSchoolLocation($schoolLocation, $logger = false, $thowException = true)
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

            if (!$thowException) {
                return false;
            }

            throw new \Exception($msg);

        }
        return $periods->first();
    }
}

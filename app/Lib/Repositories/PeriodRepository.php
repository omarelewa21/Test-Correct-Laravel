<?php namespace tcCore\Lib\Repositories;

use Carbon\Carbon;
use tcCore\Period;

class PeriodRepository {
    public static function getCurrentPeriod() {
        $now = Carbon::now();
        $result = Period::filtered()->where('start_date', '<=', $now->toDateString())->where('end_date', '>=', $now->toDateString())->first();
        return $result;
    }

    public static function getCurrentOrPreviousPeriod() {
        $now = Carbon::now();
        $result = Period::filtered()->where('start_date', '<=', $now->toDateString())->where('end_date', '>=', $now->toDateString())->first();

        if ($result === null) {
            $maxDate = Period::filtered()->max('end_date');
            $result = Period::filtered()->where('start_date', '<=', $maxDate)->where('end_date', '>=', $maxDate)->first();
        }

        return $result;
    }

    public static function getPeriodsOfCurrentOrPreviousSchoolYear() {
        $period = static::getCurrentOrPreviousPeriod();

        $results = Period::filtered()->where('school_year_id', $period->getAttribute('school_year_id'))->get();

        return $results;
    }
}
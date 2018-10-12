<?php namespace tcCore\Lib\Repositories;

use Illuminate\Database\Eloquent\Collection;
use tcCore\SchoolYear;
use tcCore\User;

class SchoolYearRepository {
    public static function getCurrentOrPreviousSchoolYear() {
        $period = PeriodRepository::getCurrentOrPreviousPeriod();
        return $period->schoolYear;
    }

    public static function getCurrentOrPreviousSchoolYearsOfStudent(User $student) {
        $schoolYears = SchoolYear::join('periods AS school_year_periods', 'school_year_periods.school_year_id', '=', 'school_years.id')
            ->join('periods', 'periods.school_year_id', '=', 'school_years.id')
            ->join('ratings', 'periods.id', '=', 'ratings.period_id')
            ->where('ratings.user_id', '=', $student->getKey())
            ->groupBy('school_years.id')
            ->havingRaw('MIN(school_year_periods.start_date) <= CURDATE() AND MAX(school_year_periods.end_date) >= CURDATE()')
            ->get(['school_years.*']);

        if ($schoolYears->isEmpty()) {
            $schoolYears = new Collection(
                [
                    SchoolYear::join('periods AS school_year_periods', 'school_year_periods.school_year_id', '=', 'school_years.id')
                    ->join('periods', 'periods.school_year_id', '=', 'school_years.id')
                    ->join('ratings', 'periods.id', '=', 'ratings.period_id')
                    ->where('ratings.user_id', '=', $student->getKey())
                    ->groupBy('school_years.id')
                    ->orderByRaw('MAX(`school_year_periods`.`end_date`)', 'desc')
                    ->first(['school_years.*'])
                ]
            );
        }

        return $schoolYears;
    }
}
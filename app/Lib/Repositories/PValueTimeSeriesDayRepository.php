<?php

namespace tcCore\Lib\Repositories;

use Illuminate\Database\Query\Builder;
use Illuminate\Support\Facades\DB;
use tcCore\Attainment;
use tcCore\EducationLevel;
use tcCore\PValue;
use tcCore\Scopes\AttainmentScope;
use tcCore\Subject;
use tcCore\User;

class PValueTimeSeriesDayRepository
{
    /**
     * @param $startDate
     * @param $endDate
     * @return Builder
     * This method returns a queryBuilder generating 10000 dates from date 01-01-2018 so max date generated is: 2045-05-19
     */
    private static function getTimeSeries($startDate, $endDate)
    {
        return DB::table(
            DB::raw("           
                        (select adddate('2018-01-01',t3*1000 + t2*100 + t1*10 + t0) gen_date from
                        (select 0 t0 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t0,
                        (select 0 t1 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t1,
                        (select 0 t2 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t2,
                        (select 0 t3 union select 1 union select 2 union select 3 union select 4 union select 5 union select 6 union select 7 union select 8 union select 9) t3
                    ) p")
        )->whereBetween('gen_date', [$startDate, $endDate]);
    }


    public static function getForStudentBySubject(User $user, $periods, $educationLevelYears, $teachers)
    {
        $dates = self::convertPeriodsToStartAndEndDate($periods, $user);

        return Subject::filterForStudentCurrentSchoolYear($user)
            ->crossJoinSub(self::getTimeSeries($dates->start_date, $dates->end_date), 'dates')
            ->leftJoinSub(self::getScoresWithSubjectId($user, $periods, $educationLevelYears, $teachers), 'p_value_query', function ($join) {
                $join->on('gen_date', '=', 'p_value_created_at')
                    ->on('subjects.id', '=', 'p_value_query.subject_id');
            })->orderByRaw('name, gen_date')
            ->get(['name', 'gen_date', 'score']);
    }

    private static function getScoresWithSubjectId(User $user, $periods, $educationLevelYears, $teachers)
    {
        return PValue::SelectRaw('avg(score/max_score) as score')
            ->selectRaw('date(p_values.created_at) as p_value_created_at')
            ->addSelect([
                'subject_id' => 'p_values.subject_id',
            ])
            ->join('test_participants', function ($join) use ($user) {
                $join->on('p_values.test_participant_id', '=', 'test_participants.id')
                    ->where('test_participants.user_id', '=', $user->getKey());
            })
            ->filter($user, $periods, $educationLevelYears, $teachers)
            ->groupBy([
                'subject_id',
                DB::raw('date(p_values.created_at)')
            ]);
    }

    public static function getForStudentForSubjectByAttainment(User $user, Subject $subject, $periods, $educationLevelYears, $teachers, $isLearningGoal)
    {
        $dates = PValueRepository::convertPeriodsToStartAndEndDate($periods, $user);

        return Attainment::withoutGlobalScope(AttainmentScope::class)
            ->whereIn('base_subject_id', Subject::select('base_subject_id')->where('id', $subject->id))
            ->whereNull('attainments.attainment_id')
            ->where('is_learning_goal', $isLearningGoal)
            ->where('attainments.education_level_id', EducationLevel::getLatestForStudentWithSubject($user, $subject)->id)
            ->crossJoinSub(self::getTimeSeries($dates->start_date, $dates->end_date), 'dates')
            ->leftJoinSub(self::getScoresWithAttainmentId($user, $periods, $educationLevelYears, $teachers), 'p_value_query', function ($join) {
                $join->on('gen_date', '=', 'p_value_created_at')
                    ->on('attainments.id', '=', 'p_value_query.attainment_id');
            })->orderByRaw('attainments.id , gen_date')
            ->get(['attainments.id', 'gen_date', 'score']);
    }

    private static function getScoresWithAttainmentId(User $user, $periods, $educationLevelYears, $teachers)
    {
        return PValue::SelectRaw('avg(score/max_score) as score')
            ->selectRaw('date(p_values.created_at) as p_value_created_at')
            ->addSelect([
                'attainment_id' => 'p_value_attainments.attainment_id',
            ])
            ->join('p_value_attainments', 'p_value_attainments.p_value_id', 'p_values.id')
            ->join('test_participants', function ($join) use ($user) {
                $join->on('p_values.test_participant_id', '=', 'test_participants.id')
                    ->where('test_participants.user_id', '=', $user->getKey());
            })
            ->filter($user, $periods, $educationLevelYears, $teachers)
            ->groupBy([
                'attainment_id',
                DB::raw('date(p_values.created_at)')
            ]);
    }

    public static function getForStudentForAttainmentByAttainment(User $user, Attainment $attainment, $subjectId, $periods, $educationLevelYears, $teachers, $isLearningGoal)
    {
        $dates = PValueRepository::convertPeriodsToStartAndEndDate($periods, $user);
        $subject = Subject::whereUuid($subjectId)->first();

        return Attainment::withoutGlobalScope(AttainmentScope::class)
//            ->whereIn('base_subject_id', Subject::select('base_subject_id')->where('id', $subject->id))
            ->where('attainments.attainment_id', $attainment->id)
            ->where('attainments.education_level_id', EducationLevel::getLatestForStudentWithSubject($user, $subject)->id)
            ->crossJoinSub(self::getTimeSeries($dates->start_date, $dates->end_date), 'dates')
            ->leftJoinSub(self::getScoresWithAttainmentId($user, $periods, $educationLevelYears, $teachers), 'p_value_query', function ($join) {
                $join->on('gen_date', '=', 'p_value_created_at')
                    ->on('attainments.id', '=', 'p_value_query.attainment_id');
            })->orderByRaw('attainments.id , gen_date')
            ->get(['attainments.id', 'gen_date', 'score']);
    }
}
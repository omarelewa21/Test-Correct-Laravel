<?php

namespace tcCore\Lib\Repositories;

use Illuminate\Support\Facades\DB;
use tcCore\BaseAttainment;
use tcCore\PValue;
use tcCore\Subject;
use tcCore\User;
use function Symfony\Component\String\s;

class TaxonomyRankingRepostitory
{
    /**
     *  QUERY FOR SELECTING AND RANKING SUBJECTS TOP 3
     * select subjects.name
     * , p_values.subject_id
     * , (1 - avg(p_values.score / p_values.max_score)) / (1 + sum(p_values.max_score) / 500) as formula
     * , avg(p_values.score / p_values.max_score)                                             as W
     * , sum(p_values.max_score)                                                              as Z
     * from p_values
     * inner join test_participants
     * on (p_values.test_participant_id = test_participants.id)
     * inner join subjects
     * on (subjects.id = p_values.subject_id)
     * inner join questions
     * on (questions.id = p_values.question_id)
     * where test_participants.user_id = 69000
     * AND (questions.miller <> null or questions.miller <> '' or questions.bloom <> null or questions.bloom <> '' or
     * questions.rtti <> null or questions.rtti <> '')
     * group by p_values.subject_id, subjects.name
     * having Z > 5
     * order by formula
     * limit 3
     */
    public static function getForSubjects(User $forUser, $filters)
    {
        logger(__CLASS__.':'.__METHOD__);

        $query = PValue::select(
            DB::raw('subjects.name as title'),
            DB::raw('subjects.id as id'),
            DB::raw('(1 - avg(p_values.score / p_values.max_score)) / (1 + sum(p_values.max_score) / 500) as formula'),
            DB::raw('avg(p_values.score / p_values.max_score) as W'),
            DB::raw('sum(p_values.max_score)   as Z  ')
        )
            ->join('test_participants', 'test_participants.id', '=', 'p_values.test_participant_id')
            ->join('subjects', 'subjects.id', '=', 'p_values.subject_id')
            ->join('questions', 'questions.id', '=', 'p_values.question_id')
            ->where('test_participants.user_id', $forUser->getKey())
            ->where(function ($query) {
                $query
                    ->orWhereNotNull('questions.miller')
                    ->orWhereRaw("questions.miller <> ''")
                    ->orWhereNotNull('questions.bloom')
                    ->orWhereRaw("questions.bloom <> ''")
                    ->orWhereNotNull('questions.rtti')
                    ->orWhereRaw("questions.rtti <> ''");
            })
            ->filter($filters['periods'], $filters['education_level_years'], $filters['teachers'])
            ->groupBy('id', 'title')
            ->having('Z', '>', 5)
            ->orderBy('formula', 'desc')
            ->orderBy('title')
            ->take(3);

        return $query->get();
    }

    /**
     * select
     * attainments.`description`
     * ,  attainments.id
     * ,(1-avg(p_values.score/p_values.max_score)) / (1+sum(p_values.max_score)/500) as formula
     * , avg(p_values.score/p_values.max_score) as W
     * , sum(p_values.max_score) as Z
     *
     * from p_values
     * inner join test_participants on (p_values.test_participant_id = test_participants.id)
     * inner join p_value_attainments on (p_values.id = p_value_attainments.p_value_id)
     * inner join attainments on (attainments.id = p_value_attainments.attainment_id)
     * inner join questions on (questions.id = p_values.question_id)
     *
     * where
     * test_participants.user_id = 69000
     * AND    p_values.subject_id = 183
     * AND attainments.`attainment_id` is null
     * AND (questions.miller <> null or questions.miller <> '' or questions.bloom <> null or questions.bloom <> '' or questions.rtti <> null or questions.rtti <> '')
     *
     * group by attainments.id, attainments.description
     * having Z > 5
     * order by formula, description
     * limit 3
     */

    public static function getForSubject(User $forUser, Subject $subject, $filters)
    {
        $query = self::getQueryForAttainment($forUser, $subject, $filters)
            ->whereNull('attainments.attainment_id');


        return $query->get();
    }

    public static function getForAttainment(User $forUser, Subject $subject, BaseAttainment $baseAttainment, $filters)
    {
        $query = self::getQueryForAttainment($forUser, $subject, $filters)
            ->where('attainments.attainment_id', $baseAttainment->getKey());


        return $query->get();
    }

    private static function getQueryForAttainment(User $forUser, Subject $subject, $filters)
    {
        return PValue::select(
            DB::raw('attainments.description as title'),
            DB::raw('attainments.id as id'),
            DB::raw('(1 - avg(p_values.score / p_values.max_score)) / (1 + sum(p_values.max_score) / 500) as formula'),
            DB::raw('avg(p_values.score / p_values.max_score) as W'),
            DB::raw('sum(p_values.max_score)   as Z  ')
        )
            ->join('test_participants', 'test_participants.id', '=', 'p_values.test_participant_id')
            ->join('p_value_attainments', 'p_values.id', '=', 'p_value_attainments.p_value_id')
            ->join('attainments', 'attainments.id', '=', 'p_value_attainments.attainment_id')
            ->join('questions', 'questions.id', '=', 'p_values.question_id')
            ->where('test_participants.user_id', $forUser->getKey())
            ->where('p_values.subject_id', $subject->getKey())
            ->where(function ($query) {
                $query
                    ->orWhereNotNull('questions.miller')
                    ->orWhereRaw("questions.miller <> ''")
                    ->orWhereNotNull('questions.bloom')
                    ->orWhereRaw("questions.bloom <> ''")
                    ->orWhereNotNull('questions.rtti')
                    ->orWhereRaw("questions.rtti <> ''");
            })
            ->filter($filters['periods'], $filters['education_level_years'], $filters['teachers'], $filters['isLearningGoal'])
            ->groupBy('id', 'title')
            ->having('Z', '>', 5)
            ->orderBy('formula', 'desc')
            ->orderBy('title')
            ->take(3);
    }
}

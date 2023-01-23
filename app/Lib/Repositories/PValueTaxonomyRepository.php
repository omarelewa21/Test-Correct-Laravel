<?php

namespace tcCore\Lib\Repositories;

use Illuminate\Database\Eloquent\Builder;
use tcCore\Attainment;
use tcCore\PValue;
use tcCore\User;

abstract class PValueTaxonomyRepository
{

    public static function getPValueForStudentForSubjectTaxonomy(User $user, $taxonomy, $subject_id, $periods = null, $educationLevelYears = null, $teachers = null, $isLearningGoal=null)
    {
        return self::getPValueForStudentTaxonomy($taxonomy, $user, $periods, $educationLevelYears, $teachers, $isLearningGoal)
            ->where('p_values.subject_id', $subject_id)
            ->get();
    }

    public static function getPValueForStudentForAttainmentTaxonomy(User $user, $taxonomy, $attainment_id, $periods = null, $educationLevelYears = null, $teachers = null, $isLearningGoal = null)
    {
        return self::getPValueForStudentTaxonomy($taxonomy, $user, $periods, $educationLevelYears, $teachers, $isLearningGoal)
            ->join('p_value_attainments', 'p_values.id', '=', 'p_value_attainments.p_value_id')
            ->where('p_value_attainments.attainment_id', $attainment_id)
            ->get();
    }

    public static function fillTaxonomyResponseWithData($data, $struct)
    {
        foreach ($data as $row) {
            $struct[$row->taxonomy] = [
                'score' => $row->score,
                'count' => $row->cnt,
            ];
        }

        $result = [];
        foreach ($struct as $taxononomy => $scoreAndCount) {
            $result[] = [
                $taxononomy,
                $scoreAndCount['score'],
                $scoreAndCount['count']
            ];
        }

        return $result;
    }

    public static function createEmptyTaxonomyResponse($options)
    {
        return collect($options)->mapWithKeys(fn($value) => [$value => ['score' => 0, 'count' => 0]])->toArray();
    }

    public static function getPValueForStudentForAttainment(User $user, $attainment_id, $periods, $educationLevelYears, $teachers, $isLearningGoal)
    {
        return self::fillTaxonomyResponseWithData(
            self::getPValueForStudentForAttainmentTaxonomy($user, static::DATABASE_FIELD, $attainment_id, $periods, $educationLevelYears, $teachers, $isLearningGoal),
            self::createEmptyTaxonomyResponse(static::OPTIONS)
        );
    }

    public static function getPValueForStudentForSubject(User $user, $subject_id, $periods, $educationLevelYears, $teachers)
    {
        return self::fillTaxonomyResponseWithData(
            self::getPValueForStudentForSubjectTaxonomy($user, static::DATABASE_FIELD, $subject_id, $periods, $educationLevelYears, $teachers),
            self::createEmptyTaxonomyResponse(static::OPTIONS)
        );
    }

    /**
     * @param $taxonomy
     * @param User $user
     * @param $periods
     * @param $educationLevelYears
     * @param $teachers
     * @return PValue
     */
    public static function getPValueForStudentTaxonomy($taxonomy, User $user, $periods, $educationLevelYears, $teachers, $isLearningGoal): Builder
    {
        abort_if(!in_array($taxonomy, ['miller', 'bloom', 'rtti']),
            404,
            sprintf('%s::%s was called with invalid taxonomy: %s', __CLASS__, __METHOD__, $taxonomy)
        );

        return PValue::selectRaw(
            sprintf('
                avg(p_values.score/p_values.max_score) as score,
                count(questions.%s) as cnt,
                questions.%s as taxonomy',
                $taxonomy,
                $taxonomy
            )
        )

            ->join('test_participants', function ($join) use ($user) {
                $join->on('p_values.test_participant_id', '=', 'test_participants.id')
                    ->where('test_participants.user_id', '=', $user->getKey());
            })
            ->join('questions', 'p_values.question_id', 'questions.id')
            ->filter($user, $periods, $educationLevelYears, $teachers, null)
            ->where(function ($query) use ($taxonomy) {
                $query->where(sprintf('questions.%s', $taxonomy), '<>', '')
                    ->WhereNotNull(sprintf('questions.%s', $taxonomy));
            })
            ->groupBy(sprintf('questions.%s', $taxonomy));
    }
}

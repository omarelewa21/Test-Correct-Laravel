<?php

namespace tcCore\Lib\Repositories;

use Illuminate\Database\Eloquent\Builder;
use tcCore\Attainment;
use tcCore\PValue;
use tcCore\User;

abstract class PValueTaxonomyRepository
{

    public static function getPValueForStudentForSubjectTaxonomy(User $user, $taxonomy, $subject_id, $periods = null, $educationLevelYears = null, $teachers = null)
    {
        return self::getPValueForStudentTaxonomy($taxonomy, $user, $periods, $educationLevelYears, $teachers)
            ->where('p_values.subject_id', $subject_id)
            ->get();
    }

    public static function getPValueForStudentForAttainmentTaxonomy(User $user, $taxonomy, $attainment_id, $periods = null, $educationLevelYears = null, $teachers = null)
    {
        return self::getPValueForStudentTaxonomy($taxonomy, $user, $periods, $educationLevelYears, $teachers)
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

    public static function getPValueForStudentForAttainment(User $user, $attainment_id, $periods, $educationLevelYears, $teachers)
    {
        return self::fillTaxonomyResponseWithData(
            self::getPValueForStudentForAttainmentTaxonomy($user, static::DATABASE_FIELD, $attainment_id, $periods, $educationLevelYears, $teachers),
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
    public static function getPValueForStudentTaxonomy($taxonomy, User $user, $periods, $educationLevelYears, $teachers): Builder
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
            ->join('p_value_attainments', 'p_values.id', '=', 'p_value_attainments.p_value_id')
            ->join('test_participants', function ($join) use ($user) {
                $join->on('p_values.test_participant_id', '=', 'test_participants.id')
                    ->where('test_participants.user_id', '=', $user->getKey());
            })
            ->join('questions', 'p_values.question_id', 'questions.id')
            ->when($periods->isNotEmpty(), fn($q) => $q->whereIn('p_values.period_id', $periods->pluck('id')))
            ->when($educationLevelYears->isNotEmpty(), fn($q) => $q->whereIn('education_level_year', $educationLevelYears->pluck('id')))
            ->when($teachers->isNotEmpty(), function ($q) use ($teachers) {
                $q->join('p_value_users', 'p_value_users.p_value_id', '=', 'p_values.id')
                    ->whereIn('p_value_users.user_id', $teachers->pluck('id'));
            })
            ->where(function ($query) use ($taxonomy) {
                $query->where(sprintf('questions.%s', $taxonomy), '<>', '')
                    ->orWhereNull(sprintf('questions.%s', $taxonomy));
            })
            ->groupBy(sprintf('questions.%s', $taxonomy));
    }
}
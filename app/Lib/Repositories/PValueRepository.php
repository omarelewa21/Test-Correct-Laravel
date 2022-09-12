<?php namespace tcCore\Lib\Repositories;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use tcCore\Attainment;
use tcCore\BaseSubject;
use tcCore\PValue;
use tcCore\Subject;
use tcCore\User;

class PValueRepository
{

    public static function getPValuesForQuestion($dottedQuestionId)
    {
        return PValue::select(
            'p_value.education_level_id',
            'p_value.education_level_year',
            DB::raw('SUM(score) / SUM(max_score) as p_value'),
            DB::raw('COUNT(DISTINCT(test_participants.user_id)) as student_count')
        )
            ->with('education_level')
            ->join('test_participants', 'test_participants.id', '=', 'p_value.test_participant_id')
            ->groupBy('question_id', 'education_level_id', 'education_level_year')
            ->having('student_count', '>=', 50)->get();
    }

    public static function getPValuesTeacher($teacherIds, $dottedQuestionIds = null)
    {
        $query = PValue::select(
            DB::raw('SUM(score) / SUM(max_score) as p_value'),
            DB::raw('COUNT(DISTINCT(test_participants.user_id)) as student_count')
        )->with('education_level')
            ->join('test_participants', 'test_participants.id', '=', 'p_value.test_participant_id')
            ->join('p_value_users', 'p_values.id', '=', 'p_value_users.p_value_id');

        if (is_array($teacherIds) && count($teacherIds) > 1) {
            $query->select('p_value_users.user_id')
                ->whereIn('p_value_users.p_value_id', $teacherIds)
                ->groupBy('p_value_users.user_id');
        } else {
            $query->where('p_value_users.p_value_id', $teacherIds);
        }

        return $query->get();
    }

    public static function getPValuesForTeacherComparison(User $teacher)
    {
        $pValues = PValue::hydrate(
            DB::select(
                DB::raw('
                    SELECT *
                    FROM p_values
                    WHERE question_id IN
                    (
                        SELECT question_id FROM (
                            SELECT p_values.question_id
                            FROM p_values
                            INNER JOIN p_value_users ON p_value_users.p_value_id = p_values.id
                            WHERE p_values.question_id IN (
                                SELECT question_id FROM (
                                    SELECT p_values.question_id
                                    FROM p_values
                                    INNER JOIN p_value_users ON p_value_users.p_value_id = p_values.id
                                    WHERE p_value_users.user_id = :teacherId
                                ) AS p_values_inner
                            )
                            GROUP BY p_values.question_id
                            HAVING COUNT(DISTINCT(p_value_users.user_id)) > 1
                        ) as p_values_inner
                    );'
                ), array('teacherId' => $teacher->getKey()
                )
            )
        );
        $pValues->load('users');
        return $pValues;
    }

    public static function getPValuesForStudent(User $student, $baseSubjectOrSubject)
    {
        // Get subjects with base subject -> subjects repository
        $studentSubjects = SubjectRepository::getSubjectsOfStudent($student);

        // Get subjects of current school location(s) -> subjects repository

        if ($student->school !== null) {
            $parentSubjects = SubjectRepository::getSubjectsOfSchool($student->school);
        } elseif ($student->schoolLocation !== null) {
            $parentSubjects = SubjectRepository::getSubjectsOfSchoolLocation($student->schoolLocation);
        } else {
            $parentSubjects = new Collection();
        }

        // Compare lists of subjects
        $baseSubjects = [];
        $subjects = [];
        $addSubjects = [];
        foreach ($studentSubjects as $subject) {
            if (!$parentSubjects->where('id', $subject->getKey())->isEmpty()) {
                $subjects[] = $subject;
                continue;
            }

            $orphanTargetSubjects = $parentSubjects->where('base_subject_id', $subject->getAttribute('base_subject_id'));
            if (!$orphanTargetSubjects->isEmpty()) {
                foreach ($orphanTargetSubjects as $orphanTargetSubject) {
                    $addSubjects[$orphanTargetSubject->getKey()][] = $subject;
                }
                continue;
            }

            $baseSubjects[] = $subjects->baseSubject;
        }
        $subjects = Collection::make($subjects);
        $baseSubjects = Collection::make($baseSubjects);

        if ($baseSubjectOrSubject === null) {
            $student->setRelation('subjects', $subjects);
            $student->setRelation('baseSubjects', $baseSubjects);

            $subjectIds = null;
        } else {
            $subjectIds = [];
            if ($baseSubjectOrSubject instanceof BaseSubject) {
                if ($baseSubjects->where('id', $baseSubjectOrSubject->getKey())->isEmpty()) {
                    $subjectIds = null;
                }

                if ($subjectIds !== null) {
                    $subjectIds = $baseSubjectOrSubject->subjects()->pluck('id')->all();
                    foreach ($subjects as $subject) {
                        if (($key = array_search($subject->getKey(), $subjectIds)) !== false) {
                            unset($subjectIds[$key]);
                        }
                    }
                }
            } elseif ($baseSubjectOrSubject instanceof Subject && $subjects->where('id', $baseSubjectOrSubject->getKey())->isEmpty()) {
                $subjectIds = null;
            } else {
                $subjectIds[] = $baseSubjectOrSubject->getKey();
            }
        }

        // Get current school year start and end dates, and periods id -> SchoolYearRepository
        $schoolYears = SchoolYearRepository::getCurrentOrPreviousSchoolYearsOfStudent($student)->load('periods');
        $periodIds = [];
        foreach ($schoolYears as $schoolYear) {
            foreach ($schoolYear->periods as $period) {
                $periodIds[] = $period->getKey();
            }
        }

        // Get all pvalues and join attainments
        $pValues = PValue::whereIn('test_participant_id', $student->testParticipants()->pluck('id'))->with('attainments');
        if ($subjectIds !== null) {
            $pValues->whereIn('subject_id', $subjectIds);
        }
        $pValues = $pValues->get();

        // Sort by attainments
        $attainmentStats = [];
        foreach ($pValues as $pValue) {
            foreach ($pValue->attainments as $attainment) {
                $attainmentId = $attainment->getAttribute('attainment_id');
                if (!array_key_exists($attainmentId, $attainmentStats)) {
                    $attainmentStats[$attainmentId] = [
                        'total' => [
                            'score'    => 0,
                            'maxScore' => 0,
                            'count'    => 0
                        ]
                    ];
                }

                $attainmentStats[$attainmentId]['total']['score'] += $pValue->getAttribute('score');
                $attainmentStats[$attainmentId]['total']['maxScore'] += $pValue->getAttribute('max_score');
                $attainmentStats[$attainmentId]['total']['count']++;

                if (in_array($pValue->getAttribute('period_id'), $periodIds)) {
                    if (!array_key_exists('current', $attainmentStats[$attainmentId])) {
                        $attainmentStats[$attainmentId]['current'] = [
                            'score'    => 0,
                            'maxScore' => 0,
                            'count'    => 0
                        ];
                    }

                    $attainmentStats[$attainmentId]['current']['score'] += $pValue->getAttribute('score');
                    $attainmentStats[$attainmentId]['current']['maxScore'] += $pValue->getAttribute('max_score');
                    $attainmentStats[$attainmentId]['current']['count']++;
                }
            }
        }

        // Get all attainments
        $attainments = Attainment::whereIn('id', array_keys($attainmentStats))->get();
        foreach ($attainments as &$attainment) {
            $attainmentId = $attainment->getKey();

            if (array_key_exists($attainmentId, $attainmentStats)) {
                $value = $attainmentStats[$attainmentId]['total']['maxScore'] > 0 ? $attainmentStats[$attainmentId]['total']['score'] / $attainmentStats[$attainmentId]['total']['maxScore'] : 0;
                $attainment->setAttribute('total_p_value', $value);
                $attainment->setAttribute('total_p_value_count', $attainmentStats[$attainmentId]['total']['count']);

                $value = $attainmentStats[$attainmentId]['current']['maxScore'] ? $attainmentStats[$attainmentId]['current']['score'] / $attainmentStats[$attainmentId]['current']['maxScore'] : 0;
                if (array_key_exists('current', $attainmentStats[$attainmentId])) {
                    $attainment->setAttribute('current_p_value', ($value));
                    $attainment->setAttribute('current_p_value_count', $attainmentStats[$attainmentId]['current']['count']);
                }
            }
        }

        $student->setRelation('developedAttainments', $attainments);

        return $student;
    }

    public static function compareTeacher(User $teacher)
    {
        $pValuesRequiredPerQuestion = 20;
        $pValuesRequiredPerTeacher = 3;

        $teacherIdToCompare = $teacher->getKey();
        $pValueRepository = new PValueRepository();
        $pValues = $pValueRepository->getPValuesForTeacherComparison($teacher);

        $teacherScores = [];
        $teacherMaxScores = [];
        $teacherCount = [];
        $globalScores = [];
        $globalMaxScores = [];

        foreach ($pValues as $pValue) {
            $questionId = $pValue->getAttribute('question_id');

            foreach ($pValue->users as $user) {
                $userId = $user->getAttribute('user_id');

                if (!array_key_exists($userId, $teacherScores)) {
                    $teacherScores[$userId] = [];
                }

                if (!array_key_exists($userId, $teacherMaxScores)) {
                    $teacherMaxScores[$userId] = [];
                }

                if (!array_key_exists($userId, $teacherCount)) {
                    $teacherCount[$userId] = [];
                }

                if (!array_key_exists($questionId, $teacherScores[$userId])) {
                    $teacherScores[$userId][$questionId] = 0;
                }

                if (!array_key_exists($questionId, $teacherMaxScores[$userId])) {
                    $teacherMaxScores[$userId][$questionId] = 0;
                }

                if (!array_key_exists($questionId, $teacherCount[$userId])) {
                    $teacherCount[$userId][$questionId] = 0;
                }

                $teacherScores[$userId][$questionId] += $pValue->getAttribute('score');
                $teacherMaxScores[$userId][$questionId] += $pValue->getAttribute('max_score');
                $teacherCount[$userId][$questionId]++;
            }

            if (!array_key_exists($questionId, $globalScores)) {
                $globalScores[$questionId] = 0;
            }

            if (!array_key_exists($questionId, $globalMaxScores)) {
                $globalMaxScores[$questionId] = 0;
            }

            $globalScores[$questionId] += $pValue->getAttribute('score');
            $globalMaxScores[$questionId] += $pValue->getAttribute('max_score');
        }

        $teacherIds = array_unique(array_merge(array_keys($teacherScores), array_keys($teacherMaxScores)));
        // Unset the ID of the teacher we are comparing
        if (($key = array_search($teacherIdToCompare, $teacherIds)) !== false) {
            unset($teacherIds[$key]);
        }

        $results = [];
        foreach ($teacherIds as $teacherId) {
            $questionIds = [];
            if (array_key_exists($teacherId, $teacherScores)) {
                $questionIds = array_merge($questionIds, array_keys($teacherScores[$teacherId]));
            }
            if (array_key_exists($teacherId, $teacherMaxScores)) {
                $questionIds = array_merge($questionIds, array_keys($teacherMaxScores[$teacherId]));
            }
            $questionIds = array_unique($questionIds);

            // Not enough questions taken by students from each teacher to compare them.
            if (count($questionIds) < $pValuesRequiredPerTeacher) {
                //continue;
            }

            $ownPValues = [];
            $thisPValues = [];
            $globalPValues = [];

            foreach ($questionIds as $questionId) {
                // Question is not taken enough to use in this comparison
                if (!array_key_exists($teacherIdToCompare, $teacherCount)
                    || !array_key_exists($questionId, $teacherCount[$teacherIdToCompare])
                    || $teacherCount[$teacherIdToCompare][$questionId] < $pValuesRequiredPerQuestion
                    || !array_key_exists($teacherId, $teacherCount)
                    || !array_key_exists($questionId, $teacherCount[$teacherId])
                    || $teacherCount[$teacherId][$questionId] < $pValuesRequiredPerQuestion) {
                    continue;
                }

                $ownPValues[] = (array_key_exists($teacherIdToCompare, $teacherMaxScores) && array_key_exists($questionId, $teacherMaxScores[$teacherIdToCompare])) ?
                    ((array_key_exists($teacherIdToCompare, $teacherScores) && array_key_exists($questionId, $teacherScores[$teacherIdToCompare])) ? $teacherScores[$teacherIdToCompare][$questionId] : 0) / $teacherMaxScores[$teacherIdToCompare][$questionId]
                    : 0;

                $thisPValues[] = (array_key_exists($teacherId, $teacherMaxScores) && array_key_exists($questionId, $teacherMaxScores[$teacherId])) ?
                    ((array_key_exists($teacherId, $teacherScores) && array_key_exists($questionId, $teacherScores[$teacherId])) ? $teacherScores[$teacherId][$questionId] : 0) / $teacherMaxScores[$teacherId][$questionId]
                    : 0;

                $globalPValues[] = (array_key_exists($questionId, $globalMaxScores)) ? ((array_key_exists($questionId, $globalScores)) ? $globalScores[$questionId] : 0) / $globalMaxScores[$questionId] : 0;
            }

            // Not enough questions taken by students from each teacher to compare them.
            if (count($ownPValues) < $pValuesRequiredPerTeacher || count($thisPValues) < $pValuesRequiredPerTeacher || count($globalPValues) < $pValuesRequiredPerTeacher) {
                continue;
            }

            $result = [
                'own'    => (array_sum($ownPValues) / count($ownPValues)),
                'this'   => (array_sum($thisPValues) / count($thisPValues)),
                'global' => (array_sum($globalPValues) / count($globalPValues)),
                'count'  => count($thisPValues)
            ];
            $results[$teacherId] = $result;
        }

        $comparedTeachers = User::whereIn('id', array_keys($results))->where('school_location_id', $teacher->school_location_id)->get();
        foreach ($comparedTeachers as &$comparedTeacher) {
            $comparedTeacher->setAttribute('p_value_own', $results[$comparedTeacher->getKey()]['own']);
            $comparedTeacher->setAttribute('p_value_this', $results[$comparedTeacher->getKey()]['this']);
            $comparedTeacher->setAttribute('p_value_global', $results[$comparedTeacher->getKey()]['global']);
            $comparedTeacher->setAttribute('p_value_question_count', $results[$comparedTeacher->getKey()]['count']);
        }
        $teacher->setRelation('ComparedTeachers', $comparedTeachers);

        return $teacher;
    }

    public static function getPValueForStudentBySubject(User $user, $periods, $educationLevelYears, $teachers)
    {
        return PValue::SelectRaw('avg(score/max_score) as score')
            ->addSelect([
                'serie' => Subject::select('name')->whereColumn('id', 'p_values.subject_id')->limit(1),
                'subject_id' => 'p_values.subject_id',
            ])
            ->join('test_participants', function ($join) use ($user) {
                $join->on('p_values.test_participant_id', '=', 'test_participants.id')
                    ->where('test_participants.user_id', '=', $user->getKey());
            })
            ->when($periods->isNotEmpty(), fn($q) => $q->whereIn('p_values.period_id', $periods->pluck('id')))
            ->when($educationLevelYears->isNotEmpty(), fn($q) => $q->whereIn('education_level_year', $educationLevelYears->pluck('id')))
            ->when($teachers->isNotEmpty(), function ($q) use ($teachers) {
                $q->join('p_value_users', 'p_value_users.p_value_id', '=', 'p_values.id')
                    ->whereIn('p_value_users.user_id', $teachers->pluck('id'));
            })
            ->groupBy('subject_id')
            ->get();
    }

    public static function getPValuePerAttainmentForStudent(User $user, $periods, $educationLevelYears, $teachers)
    {
        return PValue::SelectRaw('avg(score/max_score) as score')
            ->selectRaw('count(attainment_id) as cnt')
            ->addSelect([
                'serie' => Attainment::select('description')->whereColumn('id', 'p_value_attainments.attainment_id')->limit(1),
                'attainment_id' => 'p_value_attainments.attainment_id',
            ])
            ->join('p_value_attainments', 'p_values.id', '=', 'p_value_attainments.p_value_id')
            ->join('test_participants', function ($join) use ($user) {
                $join->on('p_values.test_participant_id', '=', 'test_participants.id')
                    ->where('test_participants.user_id', '=', $user->getKey());
            })
            ->when($periods->isNotEmpty(), fn($q) => $q->whereIn('p_values.period_id', $periods->pluck('id')))
            ->when($educationLevelYears->isNotEmpty(), fn($q) => $q->whereIn('education_level_year', $educationLevelYears->pluck('id')))
            ->when($teachers->isNotEmpty(), function ($q) use ($teachers) {
                $q->join('p_value_users', 'p_value_users.p_value_id', '=', 'p_values.id')
                    ->whereIn('p_value_users.user_id', $teachers->pluck('id'));
            })
            ->groupBy('attainment_id')
            ->get();
    }

    public static function getPValuePerSubAttainmentForStudentAndAttainment(User $user, Attainment $attainment, $periods, $educationLevelYears, $teachers)
    {
        return PValue::SelectRaw('avg(score/max_score) as score')
            ->selectRaw('count(attainment_id) as cnt')
            ->addSelect([
                'serie' => Attainment::select('description')->whereColumn('id', 'p_value_attainments.attainment_id')->limit(1),
                'attainment_id' => 'p_value_attainments.attainment_id',
            ])
            ->join('p_value_attainments', 'p_values.id', '=', 'p_value_attainments.p_value_id')
            ->join('test_participants', function ($join) use ($user) {
                $join->on('p_values.test_participant_id', '=', 'test_participants.id')
                    ->where('test_participants.user_id', '=', $user->getKey());
            })
            ->when($periods->isNotEmpty(), fn($q) => $q->whereIn('p_values.period_id', $periods->pluck('id')))
            ->when($educationLevelYears->isNotEmpty(), fn($q) => $q->whereIn('education_level_year', $educationLevelYears->pluck('id')))
            ->when($teachers->isNotEmpty(), function ($q) use ($teachers) {
                $q->join('p_value_users', 'p_value_users.p_value_id', '=', 'p_values.id')
                    ->whereIn('p_value_users.user_id', $teachers->pluck('id'));
            })
            ->whereIn('p_value_attainments.attainment_id', Attainment::where('attainment_id', $attainment->getKey())->pluck('id'))
            ->groupBy('attainment_id')
            ->get();
    }
}

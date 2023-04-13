<?php namespace tcCore\Lib\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use tcCore\AverageRating;
use tcCore\BaseSubject;
use tcCore\Rating;
use tcCore\SchoolClass;
use tcCore\Student;
use tcCore\Subject;
use tcCore\User;

class AverageRatingRepository {
    public static function getCountAndAveragesForSchoolClasses($schoolClasses) {
        $schoolClassRepository = SchoolClassRepository::getInstance();

        // Get parallel classes(for averaging)
        $allSchoolClasses = [];
        $globalAverages = [];
        $onlyMainSchoolClasses = true;
        foreach($schoolClasses as $schoolClass) {
            $globalAverages[$schoolClass->getKey()] = [];
            $parallelClasses = $schoolClassRepository->getParallelSchoolClasses($schoolClass);

            foreach($parallelClasses as $parallelClass) {
                if (!array_key_exists($parallelClass->getKey(), $globalAverages)) {
                    $globalAverages[$parallelClass->getKey()] = &$globalAverages[$schoolClass->getKey()];
                }
                if ($schoolClass->getAttribute('is_main_school_class') != 1) {
                    $onlyMainSchoolClasses = false;
                }
                $allSchoolClasses[] = $parallelClass;
            }
        }

        $allSchoolClasses = Collection::make(array_unique($allSchoolClasses));

        // Get all student in all classes
        $students = Student::whereIn('class_id', $allSchoolClasses->pluck('id'))->with('user')->get();
        $usersSchoolClassIds = [];
        $studentCount = [];
        foreach($students as $student) {
            if ($student->user === null || $student->user->getAttribute('deleted_at') !== null) {
                continue;
            }

            if (!array_key_exists($student->getAttribute('class_id'), $studentCount)) {
                $studentCount[$student->getAttribute('class_id')] = 0;
            }
            $studentCount[$student->getAttribute('class_id')]++;
            $usersSchoolClassIds[$student->getAttribute('user_id')][] = $student->getAttribute('class_id');
        }

        unset($students);

        $averages = AverageRating::whereIn('user_id', array_keys($usersSchoolClassIds))->get();
        $schoolClassAverages = [];
        foreach($averages as $average) {
            if (!array_key_exists($average->getAttribute('user_id'), $usersSchoolClassIds)) {
                continue;
            }

            $userSchoolClassIds = $usersSchoolClassIds[$average->getAttribute('user_id')];
            foreach($userSchoolClassIds as $schoolClassId) {
                $schoolClass = $allSchoolClasses->where('id', $schoolClassId)->first();

                if ($schoolClassId == $average->getAttribute('school_class_id') || ($schoolClass->getAttribute('is_main_school_class') == 1 && $onlyMainSchoolClasses === true)) {
                    $schoolClassAverages[$schoolClassId][$average->getAttribute('subject_id')][] = $average->getAttribute('rating');
                }
            }
        }
        unset($averages);

        $subjectIds = [];
        foreach($schoolClassAverages as $schoolClassId => &$schoolClassAverage) {
            foreach($schoolClassAverage as $subjectId => &$subjectAverage) {
                $subjectIds[] = $subjectId;
                $subjectAverage = array_sum($subjectAverage) / count($subjectAverage);
                $globalAverages[$schoolClassId][$subjectId][] = $subjectAverage;
            }
        }

        foreach($globalAverages as &$globalAverage) {
            foreach ($globalAverage as $subjectId => &$globalSubjectAverage) {
                $subjectIds[] = $subjectId;
                if (!is_array($globalSubjectAverage)) {
                    continue;
                }
                $globalSubjectAverage = array_sum($globalSubjectAverage) / count($globalSubjectAverage);
            }
        }

        $subjectIds = array_unique($subjectIds);
        $subjects = Subject::withTrashed()->whereIn('id', $subjectIds)->get()->keyBy('id');
        foreach($schoolClasses as $schoolClass) {
            $subjectIds = array();

            if (array_key_exists($schoolClass->getKey(), $schoolClassAverages)) {
                $subjectIds = array_merge($subjectIds, array_keys($schoolClassAverages[$schoolClass->getKey()]));
            }

            if (array_key_exists($schoolClass->getKey(), $globalAverages)) {
                $subjectIds = array_merge($subjectIds, array_keys($globalAverages[$schoolClass->getKey()]));
            }

            $subjectIds = array_unique($subjectIds);
            $schoolClassSubjects = array();
            foreach($subjectIds as $subjectId) {
                $subject = clone $subjects[$subjectId];
                if (array_key_exists($schoolClass->getKey(), $schoolClassAverages) && array_key_exists($subjectId, $schoolClassAverages[$schoolClass->getKey()])) {
                    $subject->setAttribute('average', $schoolClassAverages[$schoolClass->getKey()][$subjectId]);
                } else {
                    $subject->setAttribute('average', null);
                }

                if (array_key_exists($schoolClass->getKey(), $globalAverages) && array_key_exists($subjectId, $globalAverages[$schoolClass->getKey()])) {
                    $subject->setAttribute('global_average', $globalAverages[$schoolClass->getKey()][$subjectId]);
                } else {
                    $subject->setAttribute('global_average', null);
                }

                $schoolClassSubjects[] = $subject;
            }

            if (array_key_exists($schoolClass->getKey(), $studentCount)) {
                $schoolClass->setAttribute('student_count', $studentCount[$schoolClass->getKey()]);
            } else {
                $schoolClass->setAttribute('student_count', null);
            }

            $schoolClass->setRelation('subjects', Collection::make($schoolClassSubjects));
        }

        return $schoolClasses;
    }

    public static function getAveragesOfSubjectWithinSchoolClasses($wantedSchoolClassSubjects) {
        // Get all average scores of all students for these school classes and subjects
        return AverageRating::where(function ($query) use ($wantedSchoolClassSubjects) {
            $first = true;
            foreach($wantedSchoolClassSubjects as $schoolClassId => $wantedSchoolClassSubject) {
                $subWhere = function ($query) use ($schoolClassId, $wantedSchoolClassSubject) {
                    $query->where('school_class_id', $schoolClassId)->whereIn('subject_id', $wantedSchoolClassSubject);
                };

                if ($first === true) {
                    $first = false;
                    $query->where($subWhere);
                } else {
                    $query->orWhere($subWhere);
                }
            }
        })->get();
    }

    public static function getAveragesOfStudentsWithinSchoolClasses(SchoolClass $schoolClass) {
        return $schoolClass->load(['studentUsers', 'studentUsers.averageRatings' => function ($query) use ($schoolClass) {
            $query->where('school_class_id', $schoolClass->getKey());
        }]);
    }

    /**
     * @param User $student
     * @param null|BaseSubject|Subject $baseSubjectOrSubject
     * @param bool $scorePercentage
     */
    public static function getAverageOverTimeOfStudent(User $student, $baseSubjectOrSubject = null, $scorePercentage = false) {
        
        // Get subjects with base subject -> subjects repository
        $studentSubjects = SubjectRepository::getSubjectsOfStudent($student);

        // Get subjects of current school location(s) -> subjects repository

        if($student->school !== null) {
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
        foreach($studentSubjects as $subject) {
            if (!$parentSubjects->where('id', $subject->getKey())->isEmpty()) {
                $subjects[] = $subject;
                continue;
            }

            $orphanTargetSubjects = $parentSubjects->where('base_subject_id', $subject->getAttribute('base_subject_id'));
            if (!$orphanTargetSubjects->isEmpty()) {
                foreach($orphanTargetSubjects as $orphanTargetSubject) {
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
            return;
        }

        $subjectIds = [];
        if ($baseSubjectOrSubject instanceof BaseSubject) {
            if ($baseSubjects->where('id', $baseSubjectOrSubject->getKey())->isEmpty()) {
                return;
            }

            $subjectIds = $baseSubjectOrSubject->subjects()->pluck('id')->all();
            foreach($subjects as $subject) {
                if(($key = array_search($subject->getKey(), $subjectIds)) !== false) {
                    unset($subjectIds[$key]);
                }
            }
        } elseif ($baseSubjectOrSubject instanceof Subject && $subjects->where('id', $baseSubjectOrSubject->getKey())->isEmpty()) {
            return;
        } else {
            $subjectIds[] = $baseSubjectOrSubject->getKey();
        }

        // Get current school year start and end dates, and periods id -> SchoolYearRepository
        $schoolYears = SchoolYearRepository::getCurrentOrPreviousSchoolYearsOfStudent($student)->load('periods');
        $periodIds = [];
        foreach($schoolYears as $schoolYear) {
            foreach($schoolYear->periods as $period) {
                $periodIds[] = $period->getKey();
            }
        }

        // Get ratings with subjects within period ids OR (within dates but then only from the student)
        $schoolClassIdsBuilder = Rating::whereIn('period_id', $periodIds)->whereIn('subject_id', $subjectIds)->where('user_id', $student->getKey())->distinct()->select('school_class_id');
        
        $ratings = Rating::whereIn('ratings.school_class_id', $schoolClassIdsBuilder)
            ->whereIn('subject_id', $subjectIds)
            ->join('test_participants', 'test_participants.id', '=', 'ratings.test_participant_id')
            ->join('test_takes', 'test_takes.id', '=', 'test_participants.test_take_id')
            ->join('periods', 'periods.id', '=', 'ratings.period_id')
            ->orderBy('test_takes.time_start', 'asc')
            ->get(['ratings.*', 'test_takes.time_start', 'periods.school_year_id']);
        
        //logger(json_decode($ratings,true));
        /*
        $student->load(['ratings' => function($query) use ($subjectIds, $schoolClassIds) {
            $query->whereIn('subject_id', $subjectIds)
                ->whereIn('ratings.school_class_id', $schoolClassIds)
                ->join('test_participants', 'test_participants.id', '=', 'ratings.test_participant_id')
                ->join('test_takes', 'test_takes.id', '=', 'test_participants.test_take_id')
                ->orderBy('test_takes.time_start', 'asc')
                ->get(['ratings.*', 'test_takes.time_start']);
        }]);
        */
        
        $student['ratings'] = $ratings;

        // Generate a rolling average per school year for student and others in his school class
        $schoolYearAverages = [];
        foreach($ratings as $rating) {
            
            $schoolYearId = $rating->getAttribute('school_year_id');
            $date = $rating->getAttribute('time_start');
            
            if ($scorePercentage === true) {
                if ($rating->getAttribute('max_score') > 0) {
                    $result = $rating->getAttribute('score') / $rating->getAttribute('max_score');
                } else {
                    $result = 0;
                }
            } else {
                $result = $rating->getAttribute('rating');
            }
            $weight = $rating->getAttribute('weight');
            $userId = $rating->getAttribute('user_id');

            if (!array_key_exists($schoolYearId, $schoolYearAverages)) {
                $schoolYearAverages[$schoolYearId] = [
                    'studentRatingSum' => 0,
                    'studentWeightSum' => 0,
                    'studentAverages' => [],
                    'classRatingSum' => [],
                    'classWeightSum' => [],
                    'classAverages' => [],
                    'individualClassStudentAverages' => [],
                    'object' => $schoolYears->first(function ($value, $key) use ($schoolYearId) {
                        return $value->getKey() == $schoolYearId;
                    })
                ];
            }

            if(!array_key_exists($userId, $schoolYearAverages[$schoolYearId]['classRatingSum'])) {
                $schoolYearAverages[$schoolYearId]['classRatingSum'][$userId] = 0;
            }
            $schoolYearAverages[$schoolYearId]['classRatingSum'][$userId] += $result * $weight;

            if(!array_key_exists($userId, $schoolYearAverages[$schoolYearId]['classWeightSum'])) {
                $schoolYearAverages[$schoolYearId]['classWeightSum'][$userId] = 0;
            }
            $schoolYearAverages[$schoolYearId]['classWeightSum'][$userId] += $weight;

            if(!array_key_exists($userId, $schoolYearAverages[$schoolYearId]['individualClassStudentAverages'])) {
                $schoolYearAverages[$schoolYearId]['individualClassStudentAverages'][$userId] = 0;
            }
            $schoolYearAverages[$schoolYearId]['individualClassStudentAverages'][$userId] = (! $schoolYearAverages[$schoolYearId]['classWeightSum'][$userId]) ? 0 : $schoolYearAverages[$schoolYearId]['classRatingSum'][$userId] /  $schoolYearAverages[$schoolYearId]['classWeightSum'][$userId];

            $schoolYearAverages[$schoolYearId]['classAverages'][$date] = (! count($schoolYearAverages[$schoolYearId]['individualClassStudentAverages'])) ? 0 : (array_sum($schoolYearAverages[$schoolYearId]['individualClassStudentAverages']) / count($schoolYearAverages[$schoolYearId]['individualClassStudentAverages']));

            if ($student->getKey() == $rating->getAttribute('user_id')) {
                $schoolYearAverages[$schoolYearId]['studentRatingSum'] += $result * $weight;
                $schoolYearAverages[$schoolYearId]['studentWeightSum'] += $weight;
                $schoolYearAverages[$schoolYearId]['studentAverages'][$date] = (! $schoolYearAverages[$schoolYearId]['studentWeightSum']) ? 0 : ($schoolYearAverages[$schoolYearId]['studentRatingSum'] / $schoolYearAverages[$schoolYearId]['studentWeightSum']);
            }
        }

        // Attach average arrays to school year within subject
        if ($baseSubjectOrSubject instanceof BaseSubject) {
            $baseSubjectOrSubject = $baseSubjects->first(function ($value, $key) use ($baseSubjectOrSubject) {
                return $value->getKey() == $baseSubjectOrSubject->getKey();
            });
        } elseif ($baseSubjectOrSubject instanceof Subject) {
            $baseSubjectOrSubject = $subjects->first(function ($value,$key) use ($baseSubjectOrSubject) {
                return $value->getKey() == $baseSubjectOrSubject->getKey();
            });
        }

        $schoolYears = [];
        foreach($schoolYearAverages as $schoolYearAverage) {
            $schoolYear = $schoolYearAverage['object'];
            $schoolYear->setAttribute('classAverages', $schoolYearAverage['classAverages']);
            $schoolYear->setAttribute('studentAverages', $schoolYearAverage['studentAverages']);
            $schoolYears[] = $schoolYear;
        }

        $baseSubjectOrSubject->setRelation('SchoolYears', Collection::make($schoolYears));
        $student->setRelation('subjects', $subjects);
        $student->setRelation('baseSubjects', $baseSubjects);
        return;
    }

    /**
     * Get the subject averages for students
     * @param \Illuminate\Database\Eloquent\Collection $students
     */
    public static function getSubjectAveragesOfStudents($students) {
        $students->load(['studentSchoolClasses' => function ($query) {
            $schoolYear = SchoolYearRepository::getCurrentOrPreviousSchoolYear();
            $query->where('school_year_id', $schoolYear->getKey());
        }]);

        $schoolClasses = [];
        foreach($students as $student) {
            foreach($student->studentSchoolClasses as $schoolClass) {
                if (!array_key_exists($schoolClass->getKey(), $schoolClasses)) {
                    $schoolClasses[$schoolClass->getKey()] = $schoolClass;
                }
            }
        }

        if (!$schoolClasses) {
            return;
        }

        $students->load([
            'averageRatings' => function ($query) use ($schoolClasses) {
                $query->where('school_class_id', array_keys($schoolClasses));
            },
            'averageRatings.subject'       => function ($query) {
                $query->withTrashed();
            }
        ]);

        $schoolClasses = static::getCountAndAveragesForSchoolClasses($schoolClasses);

        foreach($students as $student) {
            $mainSchoolClasses = array();
            foreach($student->averageRatings as $averageRating) {
                $schoolClassId = $averageRating->getAttribute('school_class_id');
                if (!array_key_exists($schoolClassId, $schoolClasses)) {
                    continue;
                }

                $schoolClass = $schoolClasses[$schoolClassId];
                if ($schoolClass->getAttribute('is_main_school_class') == true) {
                    $mainSchoolClasses[$schoolClassId] = $schoolClass;
                }
                $subject = $schoolClass->subjects->where('id', $averageRating->getAttribute('subject_id'))->first();
                if ($subject === null) {
                    continue;
                }

                $schoolClassAverage = $subject->getAttribute('average');
                if ($schoolClassAverage !== null) {
                    $averageRating->setAttribute('school_class_average', $schoolClassAverage);
                }

                $globalAverage = $subject->getAttribute('global_average');
                if ($globalAverage !== null) {
                    $averageRating->setAttribute('global_average', $schoolClassAverage);
                }
            }

            $student->setRelation('main_school_classes', Collection::make(array_values($mainSchoolClasses)));
        }
    }
}
<?php namespace tcCore\Lib\Repositories;

use Illuminate\Database\Eloquent\Collection;
use tcCore\AverageRating;
use tcCore\SchoolClass;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\User;

class TeacherRepository {
    public static function getTeacherParallelSchoolClasses(User $teacher) {
        $teacherLines = $teacher->teacher;

        $schoolClassIds = [];
        $schoolClassSubjects = [];
        $subjectIds = [];
        foreach($teacherLines as $teacherLine) {
            $schoolClassIds[] = $teacherLine->getAttribute('class_id');
            $schoolClassSubjects[$teacherLine->getAttribute('class_id')][] = $teacherLine->getAttribute('subject_id');
            $subjectIds[] = $teacherLine->getAttribute('subject_id');
        }

        $subjects = Subject::whereIn('id', array_unique($subjectIds))->get();

        $schoolClasses = SchoolClass::whereIn('id', $schoolClassIds)->get();
        $allSchoolClasses = [];
        $wantedSchoolClassSubjects = [];
        foreach($schoolClasses as $schoolClass) {
            $schoolClassRepository = SchoolClassRepository::getInstance();
            $parallelSchoolClasses = $schoolClassRepository->getParallelSchoolClasses($schoolClass);
            foreach($parallelSchoolClasses as $parallelSchoolClass) {
                $allSchoolClasses[$parallelSchoolClass->getKey()] = $parallelSchoolClass;
                $wantedSchoolClassSubjects[$parallelSchoolClass->getKey()] = $schoolClassSubjects[$schoolClass->getKey()];
            }
        }

        // Get all other teachers for each class and specific subject
        $teacherLines = static::getTeacherGivingSubjectOfClasses($wantedSchoolClassSubjects, true);

        // Get all average scores of all students for these school classes and subjects
        $averages = AverageRatingRepository::getAveragesOfSubjectWithinSchoolClasses($wantedSchoolClassSubjects);

        foreach($allSchoolClasses as $schoolClassId => $schoolClass) {
            $schoolClassSubjects = [];

            foreach($wantedSchoolClassSubjects[$schoolClassId] as $subjectId) {
                $schoolClassSubject = clone $subjects->where('id', $subjectId)->first();

                $subjectTeachers = $teacherLines->where('class_id', $schoolClassId)->where('subject_id', $subjectId);
                $subjectTeacherUsers = [];
                foreach($subjectTeachers as $subjectTeacher) {
                    $subjectTeacherUsers[] = $subjectTeacher->user;
                }

                $subjectAverages = $averages->where('school_class_id', $schoolClassId)->where('subject_id', $subjectId);

                if(count($subjectTeacherUsers) === 0 && count($subjectAverages) === 0) {
                    continue;
                }

                $schoolClassSubject->setRelation('Teacher', Collection::make($subjectTeacherUsers));
                $schoolClassSubject->setRelation('Average', $subjectAverages);

                $schoolClassSubjects[] = $schoolClassSubject;
            }

            if (count($schoolClassSubjects) === 0) {
                unset($allSchoolClasses[$schoolClassId]);
            }

            $schoolClass->setRelation('subject', Collection::make($schoolClassSubjects));
        }

        $teacher->setRelation('school_class_stats', Collection::make(array_values($allSchoolClasses)));
        return $teacher;
    }

    public static function getTeacherGivingSubjectOfClasses(array $schoolClassSubjectTeachers, $withUser = false) {
        $query = Teacher::where(function ($query) use ($schoolClassSubjectTeachers) {
            $first = true;
            foreach($schoolClassSubjectTeachers as $schoolClassId => $schoolClassSubject) {
                $subWhere = function ($query) use ($schoolClassId, $schoolClassSubject) {
                    $query->where('class_id', $schoolClassId)->whereIn('subject_id', $schoolClassSubject);
                };

                if ($first === true) {
                    $first = false;
                    $query->where($subWhere);
                } else {
                    $query->orWhere($subWhere);
                }
            }
        });

        if ($withUser === true) {
            $query->with('User');
        }

        return $query->get();
    }
}


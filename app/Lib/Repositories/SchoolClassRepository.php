<?php namespace tcCore\Lib\Repositories;

use Illuminate\Support\Collection;
use tcCore\SchoolClass;
use tcCore\Subject;
use tcCore\Teacher;

class SchoolClassRepository
{

    protected static $instance;
    protected $parallelSchoolClasses = array();

    protected function __construct()
    {
    }

    /**
     * @return SchoolClassRepository
     */
    public static function getInstance()
    {
        if (static::$instance === null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    public function getParallelSchoolClasses(SchoolClass $schoolClass)
    {
        if (!array_key_exists($schoolClass->getKey(), $this->parallelSchoolClasses)) {
            $parallelSchoolClasses = SchoolClass::where('school_location_id', $schoolClass->getAttribute('school_location_id'))
                ->where('education_level_id', $schoolClass->getAttribute('education_level_id'))
                ->where('school_year_id', $schoolClass->getAttribute('school_year_id'))
                ->where('education_level_year', $schoolClass->getAttribute('education_level_year'))->get();
            foreach ($parallelSchoolClasses as $parallelSchoolClass) {
                $this->parallelSchoolClasses[$parallelSchoolClass->getKey()] = &$parallelSchoolClasses;
            }
        }

        return clone $this->parallelSchoolClasses[$schoolClass->getKey()];
    }

    public static function getCompareSchoolClassToParallelSchoolClasses(SchoolClass $subjectSchoolClass)
    {
        // Fetch subjects of school class
        $subjectIds = Teacher::where('class_id', $subjectSchoolClass->getKey())->distinct()->pluck('subject_id')->all();

        $subjects = Subject::whereIn('id', array_unique($subjectIds))->get();

        // Get parallel school classes
        $repository = static::getInstance();
        $parallelSchoolClasses = $repository->getParallelSchoolClasses($subjectSchoolClass);

        $wantedSchoolClassSubjects = array();
        $allSchoolClasses = array();
        foreach ($parallelSchoolClasses as $parallelSchoolClass) {
            $wantedSchoolClassSubjects[$parallelSchoolClass->getKey()] = $subjectIds;
            $allSchoolClasses[$parallelSchoolClass->getKey()] = $parallelSchoolClass;
        }

        // Get all other teachers for each class and specific subject
        $teacherLines = TeacherRepository::getTeacherGivingSubjectOfClasses($wantedSchoolClassSubjects, true);

        // Get all average scores of all students for these school classes and subjects
        $averages = AverageRatingRepository::getAveragesOfSubjectWithinSchoolClasses($wantedSchoolClassSubjects);

        foreach ($allSchoolClasses as $schoolClassId => $schoolClass) {
            $schoolClassSubjects = [];

            foreach ($wantedSchoolClassSubjects[$schoolClassId] as $subjectId) {
                $schoolClassSubject = clone $subjects->where('id', $subjectId)?->first();
                if(!$schoolClassSubject){
                    continue;
                }

                $subjectTeachers = $teacherLines->where('class_id', $schoolClassId)->where('subject_id', $subjectId);
                $subjectTeacherUsers = [];
                foreach ($subjectTeachers as $teacher) {
                    $subjectTeacherUsers[] = $teacher->user;
                }

                $subjectAverages = $averages->where('school_class_id', $schoolClassId)->where('subject_id', $subjectId);

                if (count($subjectTeacherUsers) === 0 && count($subjectAverages) === 0) {
                    continue;
                }

                $schoolClassSubject->setRelation('Teacher', Collection::make($subjectTeacherUsers));
                $schoolClassSubject->setRelation('Average', $subjectAverages);

                $schoolClassSubjects[] = $schoolClassSubject;
            }

            if ($schoolClassId != $subjectSchoolClass->getKey()) {
                if (count($schoolClassSubjects) === 0) {
                    unset($allSchoolClasses[$schoolClassId]);
                }

                $schoolClass->setRelation('subjects_stats', Collection::make($schoolClassSubjects));
            } else {
                $subjectSchoolClass->setRelation('subjects_stats', Collection::make($schoolClassSubjects));
            }
        }

        unset($allSchoolClasses[$subjectSchoolClass->getKey()]);
        $subjectSchoolClass->setRelation('parallel_school_classes', Collection::make(array_values($allSchoolClasses)));

        return $subjectSchoolClass;
    }
}
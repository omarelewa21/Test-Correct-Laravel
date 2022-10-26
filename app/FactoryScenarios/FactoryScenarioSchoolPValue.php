<?php

namespace tcCore\FactoryScenarios;

use Carbon\Carbon;
use tcCore\BaseSubject;
use tcCore\Factories\FactoryBaseSubject;
use tcCore\Factories\FactorySchool;
use tcCore\Factories\FactorySchoolClass;
use tcCore\Factories\FactorySchoolLocation;
use tcCore\Factories\FactorySchoolYear;
use tcCore\Factories\FactorySection;
use tcCore\Factories\FactoryUser;
use tcCore\School;
use tcCore\SchoolLocation;

class FactoryScenarioSchoolPValue extends FactoryScenarioSchool
{
    protected $schoolName;
    protected $schoolLocationName;
    protected $schoolYearYear;

    protected $sectionName;
    protected $subjectName;
    protected $baseSubjectId;

    protected $schoolClassName;


    /**
     * Create a complete school scenario with the bare necessities
     * 1 school, 1 school location
     * - without shared sections, one section only
     * - one school year, one period
     * - 1 teacher, 3 students
     *
     * Subject: name 'Nederlandse Gramatica', baseSubjectId '1', section 'Nederlands',
     * One school year, with period from '1 jan / 31 dec'
     */
    public static function create()
    {
        $factory = new static;
        //every subsequent scenario get a new name SimpleSchoolGroup001, SimpleSchoolGroup002, etc.


        //create school
        $school = FactorySchool::create('PValueSchool')->school;
        //create school location, add educationLevels VWO, Gymnasium, Havo
        $schoolLocation = FactorySchoolLocation::create($school, 'PValueSchoolLocation')->addEducationlevels([
            1, 2, 3, 4, 5, 6
        ])->schoolLocation;
        //create school year and full year period for the current year
        foreach (range(0, 5) as $year) {
            $schoolYearLocation = FactorySchoolYear::create($schoolLocation, (int) Carbon::today()
                ->subYear($year)
                ->format('Y'))
                ->addPeriodFullYear()->schoolYear;
        }
        //create section and subject
        $desiredSections = [
            1, // 'Nederlands',
            24,// 'Duits',
            23, //'Frans',
            11, //Biologie,
            9, //'Natuurkunde',
            5, //'Wiskunde A',
            22, //'Engels',
        ];

        $teacherSchoolLocation = FactoryUser::createTeacher($schoolLocation, false)->user;
        $teacherSchoolLocation = FactoryUser::createTeacher($schoolLocation, false)->user;
        $teacherSchoolLocation = FactoryUser::createTeacher($schoolLocation, false)->user;

        foreach ($desiredSections as $base_subject_id) {
            $baseSubject = BaseSubject::find($base_subject_id);
            $section = FactorySection::create($schoolLocation, $baseSubject->name)
                ->addSubject($baseSubject, $baseSubject->name)->section;
        }


        //create teacher user

        //create school class with teacher and students records, add the teacher-user, create student-users
        $schoolClassLocation = FactorySchoolClass::create($schoolYearLocation, 1, $factory->schoolClassName)
            ->addTeacher($teacherSchoolLocation, $section->subjects()->first())
            ->addStudent(FactoryUser::createStudent($schoolLocation)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation)->user);

        $factory->school = $school->refresh();
        $factory->schools->add($school);

        return $factory;
    }

    protected function generateUniqueSchoolName()
    {
        for ($i = 1; $i < 20; $i++) {
            $uniqueSchoolName = $this->schoolName.sprintf("%03d", $i);
            $uniqueSchoolLocationName = $this->schoolLocationName.sprintf("%03d", $i);
            if (!School::where('name', $uniqueSchoolName)->count() && !SchoolLocation::where('name',
                    $uniqueSchoolLocationName)) {
                $this->schoolName = $uniqueSchoolName;
                $this->schoolLocationName = $uniqueSchoolLocationName;
                break;
            }
        }

    }
}

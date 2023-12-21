<?php

namespace tcCore\FactoryScenarios;

use Carbon\Carbon;
use tcCore\Factories\FactoryBaseSubject;
use tcCore\Factories\FactorySchool;
use tcCore\Factories\FactorySchoolClass;
use tcCore\Factories\FactorySchoolLocation;
use tcCore\Factories\FactorySchoolYear;
use tcCore\Factories\FactorySection;
use tcCore\Factories\FactoryUser;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\SchoolYear;

class FactoryScenarioSchoolWordLists extends FactoryScenarioSchoolSimple
{

    public static function getSectionWithSubjects(SchoolLocation $schoolLocation, FactoryScenarioSchool $factory)
    {
        return FactorySection::create($schoolLocation, $factory->sectionName)
            ->addSubject(FactoryBaseSubject::find($factory->baseSubjectId), $factory->subjectName)
            ->addSubject(FactoryBaseSubject::find($factory->baseSubjectId), $factory->subjectName . ' new')
            ->addSubject(FactoryBaseSubject::find($factory->baseSubjectId), $factory->subjectName . ' new new')->section;
    }

    protected static function addTeachersAndStudents(
        SchoolLocation              $schoolLocation,
        SchoolYear          $schoolYearLocation,
        FactoryScenarioSchoolSimple $factory,
        mixed                       $section
    ): void {
        $teacherSchoolLocation = FactoryUser::createTeacher($schoolLocation, false)->user;
        $teacherSchoolLocation2 = FactoryUser::createTeacher($schoolLocation, false)->user;
        $teacherSchoolLocation3 = FactoryUser::createTeacher($schoolLocation, false)->user;
        //create school class with teacher and students records, add the teacher-user, create student-users
        $schoolClassLocation = FactorySchoolClass::create($schoolYearLocation, 1, $factory->schoolClassName)
            ->addTeacher($teacherSchoolLocation, $section->subjects()->first())
            ->addStudent(FactoryUser::createStudent($schoolLocation)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation)->user)
            ->addTeacher($teacherSchoolLocation2, $section->subjects()->first())
            ->addTeacher($teacherSchoolLocation3, $section->subjects()->first());
    }
}
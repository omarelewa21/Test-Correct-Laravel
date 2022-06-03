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

class FactoryScenarioSchoolRandomComplex extends FactoryScenarioSchool
{
    /**
     * Create complete school scenario with randomized names.
     * Scenario with all, but minimal content:
     * 1 School, with 2 SchoolLocations
     * each 1 SchoolYear and 1 Period
     * each 1 Section, with 1 subject (same baseSubject)
     * both Sections are shared with the other SchoolLocation
     * each 1 SchoolClass, with 1 Teacher, 3 Students
     * the first teacher also has a second schoolclass in the second schoolLocation (total 3 schoolClasses)
     */
    public static function create()
    {
        $factory = new static;

        $school = FactorySchool::create()->school;
        $schoolLocation1 = FactorySchoolLocation::create($school)->addEducationlevels([1, 2, 3])->schoolLocation;
        $schoolLocation2 = FactorySchoolLocation::create($school)->addEducationlevels([1, 2, 3])->schoolLocation;

        $schoolYearLocation1 = FactorySchoolYear::create($schoolLocation1, (int)Carbon::today()->format('Y'))
            ->addPeriodFullYear()->schoolYear;
        $schoolYearLocation2 = FactorySchoolYear::create($schoolLocation2, (int)Carbon::today()->format('Y'))
            ->addPeriodFullYear()->schoolYear;

        $section1 = FactorySection::create($schoolLocation1, 'Nederlands')
            ->addSubject(FactoryBaseSubject::find(1),'Nederlandse gramatica')
            ->addSharedSchoolLocation($schoolLocation2)
            ->section;
        $section2 = FactorySection::create($schoolLocation2, 'Nederlands')
            ->addSubject(FactoryBaseSubject::find(1),'Nederlandse tekstverklaring')
            ->addSharedSchoolLocation($schoolLocation1)
            ->section;

        $teacherSchoolLocation1and2 = FactoryUser::createTeacher($schoolLocation1)->addSchoolLocation($schoolLocation2)->user;
        $teacherSchoolLocation2 = FactoryUser::createTeacher($schoolLocation1)->user;

        $schoolClassLocation1 = FactorySchoolClass::create($schoolYearLocation1)
            ->addTeacher($teacherSchoolLocation1and2, $section1->subjects()->first())
            ->addStudent(FactoryUser::createStudent($schoolLocation1)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation1)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation1)->user);
        $schoolClassLocation2 = FactorySchoolClass::create($schoolYearLocation2)
            ->addTeacher($teacherSchoolLocation2, $section2->subjects()->first())
            ->addStudent(FactoryUser::createStudent($schoolLocation2)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation2)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation2)->user);
        $schoolClassLocation2ByTeacherLocation1 = FactorySchoolClass::create($schoolYearLocation2)
            ->addTeacher($teacherSchoolLocation1and2, $section2->subjects()->first())
            ->addStudent(FactoryUser::createStudent($schoolLocation2)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation2)->user)
            ->addStudent(FactoryUser::createStudent($schoolLocation2)->user);

        $factory->school = $school->refresh();
        $factory->schools->add($school);

        return $factory;
    }



//    public static function createOld()
//    {
//        $factory = new static;
//
//        $nameSchool1 = 'F500';
//        $nameSchoolLocation1 = 'F555';
//        $nameSchoolLocation2 = 'F550';
//        $nameSchool2 = 'F600';
//        $nameSchoolLocation3 = 'F666';
//
//        //create 2 school admins,
//        //create 2 schools,
//        //create 3 school locations,
//
//        $schoolAdmin1 = FactoryUser::createAccountManager($nameSchool1)->user;
//        $schoolAdmin2 = FactoryUser::createAccountManager($nameSchool2)->user;
//
//        $school1 = FactorySchool::create($nameSchool1,$schoolAdmin1)->school;
//        $schoolLocation1 = FactorySchoolLocation::create($school1, [
//            'name'          => $nameSchoolLocation1,
//            'customer_code' => 'FACTORY-' . $nameSchoolLocation1,
//        ])->schoolLocation;
//
//        $schoolLocation2 = FactorySchoolLocation::create($school1, [
//            'name'          => $nameSchoolLocation2,
//            'customer_code' => 'FACTORY-' . $nameSchoolLocation2,
//        ])->schoolLocation;
//
//        $school2 = FactorySchool::create($nameSchool2,$schoolAdmin2)->school;
//        $schoolLocation3 = FactorySchoolLocation::create($school2, [
//            'name'          => $nameSchoolLocation3,
//            'customer_code' => 'FACTORY-' . $nameSchoolLocation3,
//        ])->schoolLocation;
//
//        $factory->schools[] = $school1;
//        $factory->schools[] = $school2;
//        $factory->schoolLocations[] = $schoolLocation1;
//        $factory->schoolLocations[] = $schoolLocation2;
//        $factory->schoolLocations[] = $schoolLocation3;
//        $factory->schoolAdmins[] = $schoolAdmin1;
//        $factory->schoolAdmins[] = $schoolAdmin2;
//
//        return $factory;
//    }
//
//    public function dd()
//    {
//        foreach ($this->schools as $school)
//        {
//            dump($school->toArray());
//        }
//        foreach ($this->schoolLocations as $schoolLocation)
//        {
//            dump($schoolLocation->toArray());
//        }
//        foreach ($this->schoolAdmins as $schoolAdmin)
//        {
//            dump($schoolAdmin->toArray());
//        }
//        die;
//    }
//
//    public function ddName()
//    {
//        foreach ($this->schools as $index => $school)
//        {
//            dump('School '.($index+1).': '.$school->name);
//        }
//        foreach ($this->schoolLocations as $index => $schoolLocation)
//        {
//            dump('SchoolLocation '.($index+1).': '.$schoolLocation->name);
//        }
//        foreach ($this->schoolAdmins as $index => $schoolAdmin)
//        {
//            dump('SchoolAdmin '.($index+1).': '.$schoolAdmin->username);
//        }
//        die;
//    }

}
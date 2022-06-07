<?php

namespace Tests\Unit\FactoryTests\SchoolFactory;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use tcCore\Factories\FactoryBaseSubject;
use tcCore\Factories\FactorySchool;
use tcCore\Factories\FactorySchoolClass;
use tcCore\Factories\FactorySchoolLocation;
use tcCore\Factories\FactorySchoolYear;
use tcCore\Factories\FactorySection;
use tcCore\Factories\FactoryUser;
use tcCore\SchoolClass;
use Tests\TestCase;

class FactorySchoolClassTest extends TestCase
{
    use DatabaseTransactions;
    use WithFaker;

    //add Mentors?
    //add Managers?


    /** @test */
    public function can_create_school_class()
    {
        $schoolLocation = FactorySchoolLocation::create(
            FactorySchool::create(
                'S700'
            )->school,
            'SL701'
        )->addEducationlevels([1, 2, 3, 10])
            ->schoolLocation;
        $schoolYear = FactorySchoolYear::create($schoolLocation, (int)Carbon::today()->format('Y'))
            ->addPeriodFullYear()->schoolYear;
        $startCount = SchoolClass::count();
        //placement/timing $startCount: during FactorySchoolYear::create() a DemoKlas is also generated

        $schoolClassFactory = FactorySchoolClass::create($schoolYear);

        $this->assertTrue($schoolClassFactory->schoolClass->exists());
        $this->assertEquals($startCount + 1, SchoolClass::count());
        $this->assertTrue($schoolLocation->fresh() == $schoolClassFactory->schoolClass->schoolLocation);
    }

    /** @test */
    public function can_create_specified_school_class()
    {
        $schoolLocation = FactorySchoolLocation::create(
            FactorySchool::create(
                'S700'
            )->school,
            'SL701'
        )->addEducationlevels([1, 2, 3, 10])
            ->schoolLocation;
        $schoolYear = FactorySchoolYear::create($schoolLocation, (int)Carbon::today()->format('Y'))
            ->addPeriodFullYear()->schoolYear;
        $startCount = SchoolClass::count();

        $educationLevel = 1; //(1 => VWO), has to be in schoolLocation->educationLevels
        $properties = [
            'education_level_year'            => 3,
            'is_main_school_class'            => 1,
            'do_not_overwrite_from_interface' => 0,
        ];

        $schoolClass = FactorySchoolClass::create($schoolYear, $educationLevel, 'School Class 1', $properties)->schoolClass;

        $this->assertEquals($startCount + 1, SchoolClass::count());
        $this->assertEquals(3, $schoolClass->education_level_year);
        $this->assertEquals(1, $schoolClass->is_main_school_class);
        $this->assertEquals(0, $schoolClass->do_not_overwrite_from_interface);
    }
    
    /** @test */
    public function can_add_students_to_school_class()
    {
        $schoolLocation = FactorySchoolLocation::create(
            FactorySchool::create(
                'S700'
            )->school,
            'SL701'
        )->addEducationlevels([1, 2, 3, 10])
            ->schoolLocation;
        $schoolYear = FactorySchoolYear::create($schoolLocation, (int)Carbon::today()->format('Y'))
            ->addPeriodFullYear()->schoolYear;
        $schoolClassFactory = FactorySchoolClass::create($schoolYear);
        $studentUser1 = FactoryUser::createStudent($schoolLocation)->user;
        $studentUser2 = FactoryUser::createStudent($schoolLocation)->user;

        $schoolClassFactory->addStudent($studentUser1);
        $schoolClassFactory->addStudent($studentUser2);

        $this->assertEquals(2, $schoolClassFactory->schoolClass->students->count());
    }
    
    /** @test */
    public function can_add_teacher_to_school_class()
    {
        $schoolLocation = FactorySchoolLocation::create(
            FactorySchool::create(
                'S700'
            )->school,
            'SL701'
        )->addEducationlevels([1, 2, 3, 10])
            ->schoolLocation;
        $schoolYear = FactorySchoolYear::create($schoolLocation, (int)Carbon::today()->format('Y'))
            ->addPeriodFullYear()->schoolYear;
        $sectionFactory = FactorySection::create($schoolLocation)->addSubject(FactoryBaseSubject::find(1),'Nederlands');

        $schoolClassFactory = FactorySchoolClass::create($schoolYear);

        $teacherUser = FactoryUser::createTeacher($schoolLocation)->user;
        $subject = $sectionFactory->section->subjects()->first();


        $schoolClassFactory->addTeacher($teacherUser, $subject);

        $this->assertEquals(1, $schoolClassFactory->schoolClass->teacher->count());
    }

    /** @test */
    public function can_create_school_class_with_a_teacher_and_students()
    {
        $schoolLocation = FactorySchoolLocation::create(
            FactorySchool::create(
                'S700'
            )->school,
            'SL701'
        )->addEducationlevels([1, 2, 3, 10])
            ->schoolLocation;
        $schoolYear = FactorySchoolYear::create($schoolLocation, (int)Carbon::today()->format('Y'))
            ->addPeriodFullYear()->schoolYear;
        $sectionFactory = FactorySection::create($schoolLocation)->addSubject(FactoryBaseSubject::find(1),'Nederlands');


        $teacherUser = FactoryUser::createTeacher($schoolLocation)->user;
        $subject = $sectionFactory->section->subjects()->first();
        $studentUser1 = FactoryUser::createStudent($schoolLocation)->user;
        $studentUser2 = FactoryUser::createStudent($schoolLocation)->user;

        $schoolClassFactory = FactorySchoolClass::create($schoolYear)
            ->addTeacher($teacherUser, $subject)
            ->addStudent($studentUser1)
            ->addStudent($studentUser2);

        $this->assertEquals(1, $schoolClassFactory->schoolClass->teacher->count());
    }


    public function important_for_teacher()
    {
        //teacher != user with role teacher
        //teacher is the pivot table of 'school_class' with a 'teacher-role User' and a 'Subject'

        //following info is for user with role teacher:
        //  if role of new user == teacher, and school_location_id != null
        //      addSchoolLocation is called, this adds the user_id and school_location_id to the switch pivot table - school_location_user

        //  to add a teacher to a second school_location, use USER->addSchoolLocation(SchoolLocation $schoolLocation)
    }


    public function important_for_education_level()
    {
        //only education_levels that are linked to school_location (should)/can be added to a schoolClass
        //  UI select only shows education levels that are in SchoolLocationEducationLevels

        //so two options:
        //      only add education_levels that are allready linked to the school location
        //  or:
        //      Add education_level to school_location, while adding it to a new schoolClass
    }
}
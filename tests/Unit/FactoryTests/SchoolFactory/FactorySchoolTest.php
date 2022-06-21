<?php

namespace Tests\Unit\FactoryTests\SchoolFactory;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\DB;
use tcCore\EducationLevel;
use tcCore\Factories\FactorySchool;
use tcCore\Factories\FactorySchoolLocation;
use tcCore\Factories\FactoryUser;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\SchoolLocationEducationLevel;
use tcCore\User;
use Tests\TestCase;

//creating a school_location, generates the following demo records:
// 1 Section, 1 Subject, 6 users => 5 Students, 1 Teacher (users, not Student or Teacher Table records)
class FactorySchoolTest extends TestCase
{

    use DatabaseTransactions;
    use WithFaker;

    public function stappenplan_school()
    {
        //todo needed:
        //  1 school met:
        //      2 school locations
        //  1 school_location (zonder school? in test database hebben alle school_locations een School.
        //                         in geval dat school nodig is, 2de school maken voor losse school_location)
        //  5 teachers:
        //      1 in beide school_locations
        //      3 verdeeld over een van de twee school_locations (2 in school_location 1, 1 in school_location 2)
        //      1 in losse 'verkeerde' school_location (behoort niet tot school/scholengemeenschap van 1 en 2)
        // .
        //  11 Subject:                     BaseSubjects:   Sectie:
        //      Chinees                     =>  Chinees(71) =>  Klassieke talen ??? deze klopt waarschijnlijk niet.
        //      Chinees                     =>  Chinees     =>  Chinees
        //      Chinese leesvaardigheid     =>  Chinees     =>  Chinees
        //      Chinese tekstverwerking     =>  Chinees     =>  Chinees
        //      Russisch                    =>  Russisch(83)=>  Russisch
        //      Russische literatuur        =>  Russisch    =>  Russisch
        //      Klassiek Turks              =>  Turks(84)   =>  Turks
        //      Modern Turks                =>  Turks       =>  Turks
        //      Italiaanse literatuur       =>  Italiaans(76)=>  Italiaans
        //      Spaans                      =>  Spaans (25) =>  Spaans
        //      Kunstzinnige vormen         =>  Kunst(77)   =>  Kunst


        // schoolLocation 1 & 2: F555, F550
        // schoolLocation 3, zonder link met 1 en 2: F666

        //done
        //  create School (scholengemeenschap) and SchoolLocation (school)
        //create Section (independant of School, can belong to Many SchoolLocation, but is not allowed to belong to more than 1)
        //link Section to SchoolLocation
        //create schoolYear (belongs to many SchoolLocations)
        //create Period (belongs to SchoolYear)
        //get BaseSubjects by name
        //per BaseSubject, create subject:

        //todo
        //  create SchoolClass
        //      \tcCore\SchoolClass::create([]);        /* education_level_year is less or equal to max_years in EducationLevels */
        //  GET EducationLevel
        //      (lookupTable, so dont create new Levels)
        //  add EducationLevels to SchoolLocation
        //      fill pivot table: SchoolLocationEducationLevel
        //  create Teacher User
        //      new Factory(new User()); //generate teacher / user
        //  create Teacher PivotTable/Model
        //      (connects: schoolclass/users/subject)
        //      Teacher::create([]);
        //  create Student User
        //  create Student pivotTable/Model
        //      (connects: schoolclass/users/subject)
        //  create SCENARIOS
        //      Example document scenario
        //  finally create tcCore/Test. with the new school(_location) scenario

    }

    /** @test */
    public function school_locations_creation_includes_creating_a_school_manager()
    {
        $startcount = User::count();
        $schoolLocation = FactorySchoolLocation::create(FactorySchool::create()->school)->schoolLocation;

        //school manager role_id == 6
        $this->assertTrue($schoolLocation->users->last()->roles->contains(6));

        //important: 1 account manager, 1 demoTeacher and 5 demoStudents are also created, so assert count + 8
        $this->assertEquals($startcount+8, User::count());
    }
    
    /** @test */
    public function can_create_account_manager_with_specified_school_name_prefix()
    {
        $startcount = User::count();

        $accountManager = FactoryUser::createAccountManager("F100");

        $this->assertEquals('AM+F100@factory.test', $accountManager->user->username);
        $this->assertEquals($startcount + 1, User::count());
    }

    /** @test */
    public function can_create_school_account_manager_with_random_name()
    {
        $startcount = User::count();

        $schoolAdmin = FactoryUser::createAccountManager();

        $this->assertTrue($schoolAdmin->user->exists());
        $this->assertEquals($startcount + 1, User::count());
    }

    /** @test */
    public function can_create_school_by_supplying_an_account_manager_user()
    {
        $startCount = School::count();
        $schoolAdmin = FactoryUser::createAccountManager('F500')->user;

        $schoolFactory = FactorySchool::create('F500', $schoolAdmin);

        $this->assertEquals($startCount + 1, School::count());
    }

    /** @test */
    public function can_create_a_school_without_supplying_an_account_manager_user()
    {
        $startCount = School::count();

        $schoolFactory = FactorySchool::create();

        $this->assertTrue($schoolFactory->school->user->exists());
        $this->assertEquals($startCount + 1, School::count());
    }

    /** @test */
    public function can_create_school_with_matching_admin_username()
    {
        $startCount = School::count();

        $schoolFactory = FactorySchool::create('aTESTNAME');
        //stripos returns 0 when doing: stripos('TESTNAME', 'TESTNAME') -> starting position === 0
        //  and 0 == false

        $this->assertTrue(stripos($schoolFactory->school->name, 'TESTNAME')
            && stripos($schoolFactory->accountManager->username, 'TESTNAME')
        );
        $this->assertEquals($startCount + 1, School::count());
    }

    /** @test */
    public function can_create_school_and_school_location()
    {
        $startCount = SchoolLocation::count();

        $school = FactorySchool::create()->school;

        $schoolLocationFactory = FactorySchoolLocation::create($school);

        $this->assertEquals($startCount + 1, SchoolLocation::count());
    }

    /** @test */
    public function can_create_school_and_school_location_with_specified_names()
    {
        $startCount = SchoolLocation::count();

        $school = FactorySchool::create('F500')->school;

        $schoolLocationFactory = FactorySchoolLocation::create($school, 'F555');

        $this->assertEquals('F500', $school->name);
        $this->assertEquals('F555', $schoolLocationFactory->schoolLocation->name);

        $this->assertEquals($startCount + 1, SchoolLocation::count());
    }

    /** @test */
    public function can_add_Education_level_to_School_location()
    {
        $schoolLocationFactory = FactorySchoolLocation::create(FactorySchool::create()->school);
        //Demo educationLevel is automatically added when creating schoolLocation.
        $startCount = $schoolLocationFactory->schoolLocation->educationLevels()->count();

        $available_education_level_ids = [
            1 => "VWO",
            2 => "Gymnasium",
            3 => "Havo",
            4 => "Mavo / Vmbo tl",
            5 => "Vmbo gl",
            6 => "Vmbo kb",
            7 => "Vmbo bb",
            8 => "Lwoo",
            9 => "Atheneum",
            10 => "Mavo/Havo",
            11 => "Havo/VWO",
            12 => "t/h",
            13 => "h/v",
            14 => "Demo",
            15 => "Groep",
        ];

        $educationLevelId = 1;

        $schoolLocationFactory->addEducationlevel(1);

        $this->assertEquals($startCount + 1, $schoolLocationFactory->schoolLocation->fresh()->educationLevels()->count());
    }

    /** @test */
    public function can_add_multiple_Education_levels_to_a_school_location()
    {
        $schoolLocationFactory = FactorySchoolLocation::create(FactorySchool::create()->school);
        $startCount = $schoolLocationFactory->schoolLocation->educationLevels()->count();

        $schoolLocationFactory->addEducationlevels([1,2,3,10]); //VWO, Havo, Gymnasium & Havo/VWO

        $this->assertEquals($startCount + 4, $schoolLocationFactory->schoolLocation->fresh()->educationLevels()->count());
    }
}
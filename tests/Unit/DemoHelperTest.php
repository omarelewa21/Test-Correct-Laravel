<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use tcCore\EducationLevel;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\OnboardingWizard;
use tcCore\OnboardingWizardStep;
use tcCore\OnboardingWizardUserStep;
use tcCore\Period;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\Section;
use tcCore\Subject;
use tcCore\Teacher;
use tcCore\Test;
use tcCore\TestTake;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Unit\Http\Helpers\DemoHelperTestHelper;
use Tests\Unit\Http\Helpers\OnboardingTestHelper;

class DemoHelperTest extends TestCase
{
    use DatabaseTransactions;

    /** @test */
    public function demohelper_a_teacher_should_be_created()
    {

        $helper = (new DemoHelperTestHelper());
        $schoolLocation = SchoolLocation::first();
        $helper->setSchoolLocation($schoolLocation);
        $helper->getUsername('teacher');
        $this->assertEquals(0,User::where('username',$helper->getUsername('teacher'))->count());

        $helper->createDemoTeacherIfNeeded();

        $this->assertEquals(1,User::where('username',$helper->getUsername('teacher'))->count());
        $teacher = User::where('username',$helper->getUsername('teacher'))->first();
        $this->assertTrue($teacher->isA('Teacher'));
    }

    /** @test */
    public function demohelper_students_should_be_created()
    {
        $helper = (new DemoHelperTestHelper());
        $schoolLocation = SchoolLocation::first();
        $helper->setSchoolLocation($schoolLocation);

        $userCount = User::count();

        $this->assertEquals(0,User::where('username',$helper->getUsername('student','01'))->count());

        $helper->createDemoStudentsIfNeeded();

        $this->assertEquals(1,User::where('username',$helper->getUsername('student','01'))->count());
        $teacher = User::where('username',$helper->getUsername('student','01'))->first();
        $this->assertTrue($teacher->isA('Student'));

        $this->assertEquals($userCount+5, User::count());

        $helper->createDemoStudentsIfNeeded();

        $this->assertEquals($userCount+5, User::count());
    }

    /** @test */
    public function demohelper_section_should_be_created()
    {
        $helper = (new DemoHelperTestHelper());
        $schoolLocation = SchoolLocation::first();
        $helper->setSchoolLocation($schoolLocation);

        $this->assertNull(($helper->getDemoSection()));

        $helper->createDemoSectionIfNeeded();

        $this->assertEquals(Section::class,get_class(($helper->getDemoSection())));

    }

    /** @test */
    public function demohelper_educationlevel_should_be_created_if_non_existent()
    {
        $helper = (new DemoHelperTestHelper());
        $schoolLocation = SchoolLocation::first();
        $helper->setSchoolLocation($schoolLocation);

        $this->assertEquals(EducationLevel::class,get_class(($helper->getDemoEducationLevel())));
    }

    /** @test */
    public function demohelper_subject_should_be_created_if_non_existent()
    {
        $helper = (new DemoHelperTestHelper());
        $schoolLocation = SchoolLocation::first();
        $helper->setSchoolLocation($schoolLocation);
        $helper->createDemoSectionIfNeeded();
        $count = Subject::count();
        $this->assertEquals(Subject::class,get_class(($helper->createDemoSubjectIfNeeded())));
        $this->assertEquals($count+1, Subject::count());
        $this->assertEquals(Subject::class,get_class(($helper->createDemoSubjectIfNeeded())));
        $this->assertEquals($count+1, Subject::count());
    }

    /** @test */
    public function demohelper_a_new_teacher_should_get_a_demotest()
    {
        $helper = (new DemoHelperTestHelper());
        $schoolLocation = SchoolLocation::first();
        $helper->setSchoolLocation($schoolLocation);
        $helper->createDemoSectionIfNeeded();
        $subject = $helper->createDemoSubjectIfNeeded();
        $actingUser = $this->getActingSchoolbeheerder();
        $this->actingAs($actingUser);
        $schoolYear = SchoolYearRepository::getCurrentSchoolYear();
        if($schoolYear === null){
            $schoolYear = $schoolLocation->schoolLocationSchoolYears->first()->schoolYear;
            Period::create([
                'school_year_id' => $schoolYear->getKey(),
                'name' => 'demohelpertest',
                'start_date' => Carbon::today()->sub(CarbonInterval::days(2)),
                'end_date' => Carbon::today()->add(CarbonInterval::days(2)),
            ]);
        }

        if(Test::where('name',DemoHelperTestHelper::BASEDEMOTESTNAME)->count() === 0){
            $test = Test::first();
            $test->name = DemoHelperTestHelper::BASEDEMOTESTNAME;
            $test->save();
        }

        $user = User::where('username','d1@test-correct.nl')->first();

        $returnData = (object) $helper->prepareDemoForNewTeacher($schoolLocation,$schoolYear,$user);
        $this->assertTrue($returnData->new);

        $this->assertEquals(Test::class,get_class($returnData->test));

        $baseTestTakesCount = TestTake::where('test_id',(new DemoHelperTestHelper)->getBaseDemoTest()->getKey())->count();
        $newTestTakesCount = count($returnData->testTakes);

        $this->assertEquals($baseTestTakesCount,$newTestTakesCount);
    }

    protected function getActingSchoolbeheerder()
    {
        return User::where('username','opensourceschoollocatie1schoolbeheerder@test-correct.nl')->first();
    }

    /** @test */
    public function demohelper_democlass_and_users_should_be_created_if_needed_on_new_current_period()
    {
        $helper = (new DemoHelperTestHelper());

        $actingUser = $this->getActingSchoolbeheerder();
        $this->actingAs($actingUser);

        $schoolLocation = $actingUser->schoolLocation;
        $helper->setSchoolLocation($schoolLocation);

        $teacherUsername = $helper->getUsername('teacher');

        $this->assertEquals(0,User::where('username',$teacherUsername)->count());

        $classCount = SchoolClass::count();

        $schoolYear = $schoolLocation->schoolLocationSchoolYears->first()->schoolYear;
        Period::create([
            'school_year_id' => $schoolYear->getKey(),
            'name' => 'demohelpertest',
            'start_date' => Carbon::today()->sub(CarbonInterval::days(2)),
            'end_date' => Carbon::today()->add(CarbonInterval::days(2)),
        ]);

        $this->assertEquals($classCount+1,SchoolClass::count());

        $this->assertEquals(1,User::where('username',$teacherUsername)->count());
    }

    /** @test */
    public function demohelper_period_creation_should_throw_error_if_created_through_user_without_schoollocation()
    {
        $helper = (new DemoHelperTestHelper());

        $user = User::where('username','testadmin@teachandlearncompany.com')->first();
        $this->actingAs($user);

        $schoolLocation = SchoolLocation::first();
        $helper->setSchoolLocation($schoolLocation);

        $teacherUsername = $helper->getUsername('teacher');

        $this->assertEquals(0,User::where('username',$teacherUsername)->count());

        $schoolYear = $schoolLocation->schoolLocationSchoolYears->first()->schoolYear;
        try {
            Period::create([
                'school_year_id' => $schoolYear->getKey(),
                'name' => 'demohelpertest',
                'start_date' => Carbon::today()->sub(CarbonInterval::days(2)),
                'end_date' => Carbon::today()->add(CarbonInterval::days(2)),
            ]);
        }
        catch(\Exception $exception){
            $this->assertEquals('U kunt een periode alleen aanmaken als een gebruiker van een schoollocatie. Dit doet u door als schoolbeheerder in het menu Database -> Schooljaren een schooljaar aan te maken met een periode die in de huidige periode valt.',
                $exception->getMessage());
        }
    }

    /** @test */
    public function demohelper_on_schoollocation_creation_section_subject_and_users_should_be_created()
    {
        $sectionCount = Section::count();
        $subjectCount = Subject::count();
        $userCount = User::count();

        $helper = new DemoHelper();
        $schoolLocation = SchoolLocation::create([
            'name'=> Str::random(5),
            'education_levels'=> ['2'],
            'customer_code'=> Str::random(5),
            'user_id'=> '520',
            'grading_scale_id'=> '1',
            'activated'=> 0,
            'number_of_students'=> '4',
            'number_of_teachers'=> '5',
            'external_main_code'=> 'fd',
            'external_sub_code'=> 'sd',
            'is_rtti_school_location'=> '0',
            'is_open_source_content_creator'=> '0',
            'is_allowed_to_view_open_source_content'=> '0',
            'main_address'=> 'f',
            'invoice_address'=> 'd',
            'visit_address'=> 's',
            'main_postal'=> 'f',
            'invoice_postal'=> 'd',
            'visit_postal'=> 's',
            'main_city'=> 'f',
            'invoice_city'=> 'd',
            'visit_city'=> 's',
            'main_country'=> 'f',
            'invoice_country'=> 's',
            'visit_country'=> 's',
        ]);

        $this->assertEquals(Section::count(),$sectionCount+1);
        $this->assertEquals(Subject::count(),$subjectCount+1);
        $this->assertEquals(User::count(),$userCount+6);

    }
}

<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use tcCore\EducationLevel;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Jobs\SetSchoolYearForDemoClassToCurrent;
use tcCore\Lib\Repositories\PeriodRepository;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\OnboardingWizard;
use tcCore\OnboardingWizardStep;
use tcCore\OnboardingWizardUserStep;
use tcCore\Period;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\SchoolYear;
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

class SetSchoolYearForDemoClassToCurrentTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function it_can_run_the_job()
    {
        $user = \tcCore\User::where('username','=',static::USER_TEACHER)->get()->first();

        $this->post('/auth',["user" => static::USER_TEACHER,"password" => "Sobit4456"]);

        $demoClass = SchoolClass::where('school_location_id',$user->schoolLocation->getKey())
                        ->where('name',DemoHelper::CLASSNAME)->first();

        ActingAsHelper::getInstance()->setUser($user);
        $currentSchoolYear = SchoolYearRepository::getCurrentSchoolYear();

        if($currentSchoolYear == $demoClass->schoolYear){
            $demoClass->demoRestrictionOverrule = true;
            $demoClass->school_year_id = 2;
            $demoClass->save();

        }
//        dd($demoClass->refresh());
//
        $this->assertNotEquals(
            $demoClass->refresh()->schoolYear->getKey(),
            $currentSchoolYear->getKey());



        $this->assertTrue((new SetSchoolYearForDemoClassToCurrent($user->schoolLocation))->handle());

        $this->assertEquals(
            $currentSchoolYear->getKey(),
            $demoClass->refresh()->schoolYear->getKey()
        );

    }


}

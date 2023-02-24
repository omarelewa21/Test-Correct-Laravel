<?php

namespace Tests\Unit\Jobs;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use tcCore\EducationLevel;
use tcCore\Factories\FactorySchool;
use tcCore\Factories\FactorySchoolLocation;
use tcCore\Factories\FactorySchoolYear;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimpleWithTest;
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
use Tests\ScenarioLoader;
use Tests\TestCase;
use Tests\Unit\Http\Helpers\DemoHelperTestHelper;
use Tests\Unit\Http\Helpers\OnboardingTestHelper;

class SetSchoolYearForDemoClassToCurrentTest extends TestCase
{

    protected $loadScenario = FactoryScenarioSchoolSimpleWithTest::class;

    private User $teacherOne;
    private User $studentOne;


    protected function setUp(): void
    {
        parent::setUp();
        $this->teacherOne = ScenarioLoader::get('user');
        $this->studentOne = ScenarioLoader::get('student1');
    }


    /**
     * @test
     */
    public function it_can_run_the_job()
    {
        ActingAsHelper::getInstance()->setUser($this->teacherOne);
        $helper = new DemoHelper;
        $helper->alwaysCreateDemoEnvironment = true;

        $helper->createDemoForTeacherIfNeeded($this->teacherOne, true);
        $demoClass = SchoolClass::where('school_location_id', $this->teacherOne->schoolLocation->getKey())
            ->where('name', DemoHelper::CLASSNAME)
            ->first();

        ActingAsHelper::getInstance()->setUser($this->teacherOne);
        $currentSchoolYear = SchoolYearRepository::getCurrentSchoolYear();

        if ($currentSchoolYear == $demoClass->schoolYear) {
            $demoClass->demoRestrictionOverrule = true;
            $demoClass->school_year_id = FactorySchoolYear::createLastSchoolYear($this->teacherOne->schoolLocation)
                ->schoolYear
                ->getKey();
            $demoClass->save();
        }

        $this->assertNotEquals(
            $demoClass->refresh()->schoolYear->getKey(),
            $currentSchoolYear->getKey()
        );

        $this->assertTrue((new SetSchoolYearForDemoClassToCurrent($this->teacherOne->schoolLocation))->handle());

        $this->assertEquals(
            $currentSchoolYear->getKey(),
            $demoClass->refresh()->schoolYear->getKey()
        );
    }
}

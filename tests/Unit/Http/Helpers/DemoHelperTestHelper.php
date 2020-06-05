<?php


namespace Tests\Unit\Http\Helpers;


use Illuminate\Support\Str;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Http\Helpers\EduIxService;
use tcCore\OnboardingWizard;
use tcCore\OnboardingWizardStep;
use tcCore\SchoolYear;
use tcCore\Teacher;
use tcCore\User;
use Tests\TestCase;

class DemoHelperTestHelper extends DemoHelper
{
    public $schoolLocation;

    public function getBaseDemoTest()
    {
        return parent::getBaseDemoTest(); // TODO: Change the autogenerated stub
    }

    public function getUsername($type,$nr = null, $customer_code = null)
    {
        return parent::getUsername($type,$nr, $customer_code);
    }

    public function createDemoTeacherIfNeeded()
    {
        return parent::createDemoTeacherIfNeeded(); // TODO: Change the autogenerated stub
    }

    public function createDemoStudentsIfNeeded()
    {
        return parent::createDemoStudentsIfNeeded(); // TODO: Change the autogenerated stub
    }

    public function createDemoSectionIfNeeded()
    {
        return parent::createDemoSectionIfNeeded(); // TODO: Change the autogenerated stub
    }

    public function getDemoSection()
    {
        return parent::getDemoSection(); // TODO: Change the autogenerated stub
    }

    public function getDemoEducationLevel()
    {
        return parent::getDemoEducationLevel(); // TODO: Change the autogenerated stub
    }

    public function createDemoSubjectIfNeeded()
    {
        return parent::createDemoSubjectIfNeeded(); // TODO: Change the autogenerated stub
    }

    public function getTestNameForTeacher(Teacher $teacher)
    {
        return parent::getTestNameForTeacher($teacher); // TODO: Change the autogenerated stub
    }

    public function createDemoClassIfNeeded(SchoolYear $schoolYear)
    {
        return parent::createDemoClassIfNeeded($schoolYear); // TODO: Change the autogenerated stub
    }
}
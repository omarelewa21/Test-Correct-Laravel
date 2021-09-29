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
use tcCore\Services\EmailValidatorService;
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

class EmailaddressValidatorTest extends TestCase
{

    /** @test */
    public function it_can_validate_an_email_address()
    {
        $validator = new EmailValidatorService('aap.nl', 'martin@aap.nl');

        $this->assertFalse($validator->passes());

        $this->assertEquals('', $validator->getMessage());
    }
    
    /** @test */
    public function it_can_handle()
    {
        
    }

}

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
    /**
     * @test
     * @dataProvider provideData
     */
    public function it_can_validate_a_star_domain($domain, $mail, $passes, $message)
    {
        $validator = new EmailValidatorService($domain, $mail);

        $this->assertEquals($passes, $validator->passes());

        $this->assertEquals($message, $validator->getMessage());
    }

    public function provideData()
    {
        return [
            ['mail.com', 'martin@hotmail.com', false, ['@mail.com']],
            ['.aap.nl', 'martin@aap.nl', false, ['.aap.nl']],
            ['@aap.nl', 'martin@*aap.nl', false, ['@aap.nl']],
            ['@aap.nl;@me.nl', 'martin@sobit.nl', false, ['@aap.nl', '@me.nl']],
            ['*@aap.nl', 'martin@else.nl', false, ['@aap.nl']],
            ['*aap.nl', 'martin@aap.nl', true, ''],
            ['*aap.nl', 'martin@testaap.nl', true, ''],
            ['*aap.nl', 'martin@hotmail.nl', false, ['*aap.nl']],
            ['aap.nl', 'martin@aap.nl', true, ''],
            ['ap.nl', 'martin@aap.nl', false, ['@ap.nl']],
            ['*.aap.nl', 'martin@student.aap.nl', true, ''],
            ['*.aap.nl', 'martin@aap.nl', false, ['.aap.nl']],
            ['@aap.nl', 'martin@aap.nl', true, ''],
        ];
    }
}

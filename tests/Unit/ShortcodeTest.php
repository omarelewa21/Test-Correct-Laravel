<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Carbon\CarbonInterval;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use tcCore\EducationLevel;
use tcCore\FactoryScenarios\FactoryScenarioSchoolSimple;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\OnboardingWizard;
use tcCore\OnboardingWizardStep;
use tcCore\OnboardingWizardUserStep;
use tcCore\Period;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\Section;
use tcCore\Shortcode;
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

class ShortcodeTest extends TestCase
{
//    use DatabaseTransactions;
    protected $loadScenario = FactoryScenarioSchoolSimple::class;
    /** @test */
    public function it_can_be_created()
    {
        $startCount = Shortcode::count();
        Shortcode::createForUser($user = User::first());

        $this->assertEquals(++$startCount, Shortcode::count());
    }

    /** @test */
    public function when_a_shortcode_is_generated_for_two_diffent_users_they_are_stored()
    {
        $startCount = Shortcode::count();
        Shortcode::createForUser($user = User::first());
        $this->assertEquals(++$startCount, Shortcode::count());

        Shortcode::createForUser($user = User::where($user->getKeyName(), '<>', $user->getKey())->first());
        $this->assertEquals(++$startCount, Shortcode::count());

    }

    /** @test */
    public function when_a_second_shortcode_is_created_for_this_user_the_old_one_gets_deleted()
    {
        $startCount = Shortcode::count();
        $codeOne = Shortcode::createForUser($user = User::first())->code;
        $codeTwo = Shortcode::createForUser($user)->code;

        $this->assertEquals(++$startCount, Shortcode::count());
        $this->assertDatabaseHas('shortcodes', [
            'code' => $codeTwo
        ]);
        $this->assertDatabaseMissing('shortcodes', [
            'code' => $codeOne
        ]);
    }



    /** @test */
    public function a_code_generated_ago_seconds_ago_should_be_invalid()
    {
        $secondsInFuture = 2+Shortcode::MAX_VALID_IN_SECONDS;

        $codeOne = Shortcode::createForUser($user = User::first())->code;

        Carbon::setTestNow(Carbon::now()->addSeconds($secondsInFuture));

        $this->assertFalse(Shortcode::isValid($codeOne));
    }
}

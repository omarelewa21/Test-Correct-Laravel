<?php

namespace Tests\Unit;

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Str;
use tcCore\OnboardingWizard;
use tcCore\OnboardingWizardStep;
use tcCore\OnboardingWizardUserStep;
use tcCore\Test;
use tcCore\User;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Tests\Unit\Http\Helpers\OnboardingTestHelper;

class OnboardingWizardTest extends TestCase
{
    use DatabaseTransactions;


    /** @test */
    public function onboarding_a_teacher_should_have_steps_for_a_wizard()
    {
        $helper = (new OnboardingTestHelper());
        $obj = $helper->createNewWizardWithSteps();

        $user = User::where('username','d1@test-correct.nl')->first();

        $steps = $user->getOnboardingWizardSteps();
        $this->assertTrue($steps->count() === 2); // two main steps
        $this->assertTrue($steps->first()->sub->count() === 2);
        $this->assertTrue($steps->last()->sub->count() === 2);
    }

    /** @test */
    public function onboarding_a_teacher_should_have_no_done_steps_with_start_of_wizard()
    {
        $helper = (new OnboardingTestHelper());
        $obj = $helper->createNewWizardWithSteps();

        $done = $helper->countDoneStepsForUser($user = User::where('username',self::USER_TEACHER)->first());

        $this->assertTrue($done === 0);
    }

    /** @test */
    public function onboarding_a_teacher_should_have_one_done_step()
    {
        $helper = (new OnboardingTestHelper());
        $obj = $helper->createNewWizardWithSteps();

        $user = User::where('username','d1@test-correct.nl')->first();

        OnboardingWizardUserStep::create([
           'id' => Str::uuid(),
           'onboarding_wizard_step_id' => $obj->steps[0],
           'user_id' => $user->getKey()
        ]);

        $done = $helper->countDoneStepsForUser($user);

        $this->assertTrue($done === 1);
    }

    /** @test */
    public function onboarding_a_teacher_should_have_two_done_steps()
    {
        $helper = (new OnboardingTestHelper());
        $obj = $helper->createNewWizardWithSteps();

        $user = User::where('username','d1@test-correct.nl')->first();

        OnboardingWizardUserStep::create([
            'id' => Str::uuid(),
            'onboarding_wizard_step_id' => $obj->steps[0],
            'user_id' => $user->getKey()
        ]);

        OnboardingWizardUserStep::create([
            'id' => Str::uuid(),
            'onboarding_wizard_step_id' => $obj->steps[1],
            'user_id' => $user->getKey()
        ]);

        $done = $helper->countDoneStepsForUser($user);

        $this->assertTrue($done === 2);
    }

    /** @test */
    public function onboarding_a_teacher_should_have_two_done_steps_with_multiple_times_same_step()
    {
        $helper = (new OnboardingTestHelper());
        $obj = $helper->createNewWizardWithSteps();

        $user = User::where('username','d1@test-correct.nl')->first();

        OnboardingWizardUserStep::create([
            'id' => Str::uuid(),
            'onboarding_wizard_step_id' => $obj->steps[0],
            'user_id' => $user->getKey()
        ]);

        OnboardingWizardUserStep::create([
            'id' => Str::uuid(),
            'onboarding_wizard_step_id' => $obj->steps[1],
            'user_id' => $user->getKey()
        ]);

        OnboardingWizardUserStep::create([
            'id' => Str::uuid(),
            'onboarding_wizard_step_id' => $obj->steps[1],
            'user_id' => $user->getKey()
        ]);

        $done = $helper->countDoneStepsForUser($user);

        $this->assertTrue($done === 2);
    }
}

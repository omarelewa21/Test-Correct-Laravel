<?php


namespace Tests\Unit\Http\Helpers;


use Illuminate\Support\Str;
use tcCore\OnboardingWizard;
use tcCore\OnboardingWizardStep;

class OnboardingTestHelper
{

    public function countDoneStepsForUser($user){
        $user->refresh();

        $steps = $user->getOnboardingWizardSteps();

        return $this->countStepsDone($steps);
    }

    public function countStepsDone($steps){
        $done = 0;
        $steps->each(function($step) use (&$done){
            if($step->done === true) $done++;
            $step->sub->each(function($sub) use (&$done){
                if($sub->done === true) $done++;
            });
        });
        return $done;
    }

    public function createNewWizardWithSteps($overrides = [])
    {

        $wizardId = Str::uuid();
        $return = (object) [
            'wizardId' => $wizardId,
            'steps' => [],
        ];

        $attributes = array_merge([
            'id' => $wizardId,
            'title' => 'Test Wizard',
            'role_id' => 1,
            'active' => true,
        ], $overrides);

        OnboardingWizard::create($attributes);

        $parentId1 = Str::uuid();
        $parentId2 = Str::uuid();

        $step = $this->createStep([
            'onboarding_wizard_id' => $wizardId,
            'id' => $parentId1,
            'title' => 'Hoofd stap 1',
            'displayorder' => 1,
        ]);

        $return->steps[] = $step->getKey();

        $step = $this->createStep([
            'onboarding_wizard_id' => $wizardId,
            'parent_id' => $parentId1,
            'title' => 'stap 1.1',
            'displayorder' => 1
        ]);

        $return->steps[] = $step->getKey();

        $step = $this->createStep([
            'onboarding_wizard_id' => $wizardId,
            'parent_id' => $parentId1,
            'title' => 'stap 1.2',
            'displayorder' => 2
        ]);

        $return->steps[] = $step->getKey();

        $step = $this->createStep([
            'onboarding_wizard_id' => $wizardId,
            'id' => $parentId2,
            'title' => 'Hoofd stap 2',
            'displayorder' => 2,
        ]);

        $return->steps[] = $step->getKey();

        $step = $this->createStep([
            'onboarding_wizard_id' => $wizardId,
            'parent_id' => $parentId2,
            'title' => 'stap 2.1',
            'displayorder' => 1
        ]);

        $return->steps[] = $step->getKey();

        $step = $this->createStep([
            'onboarding_wizard_id' => $wizardId,
            'parent_id' => $parentId2,
            'title' => 'stap 2.2',
            'displayorder' => 2
        ]);

        $return->steps[] = $step->getKey();

        return $return;
    }

    private function createStep($overrides = [])
    {
        $attributes = array_merge([
            'id' => Str::uuid(),
            'title' => 'stap',
            'displayorder' => 1
        ], $overrides);

        return OnboardingWizardStep::create($attributes);
    }

}
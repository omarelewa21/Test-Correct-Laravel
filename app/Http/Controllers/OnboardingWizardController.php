<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use tcCore\Http\Requests;
use tcCore\Http\Requests\AddOnboardingWizardUserStepRequest;
use tcCore\OnboardingWizardReport;
use tcCore\OnboardingWizardUserStep;
use tcCore\Test;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\CreateTestRequest;
use tcCore\Http\Requests\UpdateTestRequest;

class OnboardingWizardController extends Controller {

    public function registerUserStep(AddOnboardingWizardUserStepRequest $request)
    {
        OnboardingWizardUserStep::create([
            'id' => Str::uuid(),
            'user_id' => Auth::user()->getKey(),
            'onboarding_wizard_step_id' => $request->get('onboarding_wizard_step_id')
        ]);

        $stepsCollection = $this->getStepsCollection();


        return Response::make(json_encode([
            'progress' => $stepsCollection->progress,
        ]), 200);
    }

    public function showStepsForUser(Request $request)
    {
        return Response::make(json_encode($this->getStepsCollection()), 200);
    }

    private function getStepsCollection() {
        return OnboardingWizardReport::getStepsCollection(Auth::user());
//        $steps = Auth::user()->getOnboardingWizardSteps();
//
//        $sub_steps = $steps->map(function($step) {
//            return $step->sub;
//        })->flatten(1);
//
//        $count_sub_steps = $sub_steps->count();
//        $count_sub_steps_done = $sub_steps->filter(function($substep) {
//            return $substep->done;
//        })->count();
//
//        return (object)[
//            'steps' => $steps,
//            'count_main_steps' => count($steps),
//            'count_sub_steps' => $count_sub_steps,
//            'count_sub_steps_done' => $count_sub_steps_done,
//            'progress' => floor($count_sub_steps_done/$count_sub_steps * 100),
//            'show' => Auth::user()->onboardingWizardUserState->show ?? 0,
//            'active_step' => Auth::user()->onboardingWizardUserState->active_step ?? 0,
//        ];
    }

    public function update(Request $request)
    {
        if (collect(['true', 1])->contains(request('show'))) {
            $request->merge(['show' => true]);
        } elseif (collect(['false', 0])->contains(request('show'))) {
           $request->merge(['show' => false]);
        }


        Auth::user()->onboardingWizardUserState->update($request->validate([
            'show' => 'boolean',
            'active_step' => 'integer',
        ]));
    }
}

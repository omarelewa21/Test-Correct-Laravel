<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use tcCore\AppVersionInfo;
use tcCore\Http\Requests;
use tcCore\Http\Requests\AddOnboardingWizardUserStepRequest;
use tcCore\OnboardingWizardReport;
use tcCore\OnboardingWizardUserStep;
use tcCore\Test;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\CreateTestRequest;
use tcCore\Http\Requests\UpdateTestRequest;

class AppVersionInfoController extends Controller {

    public function Store(Requests\CreateAppVersionInfoRequest $request)
    {
        AppVersionInfo::create($request->validated());

        return Response::make('ok', 200);
    }
}

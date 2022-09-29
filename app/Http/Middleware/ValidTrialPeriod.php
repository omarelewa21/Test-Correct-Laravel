<?php

namespace tcCore\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ValidTrialPeriod
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::user()->trialPeriodWithSchoolLocationCheck()->exists() && Auth::user()->trialPeriodWithSchoolLocationCheck->trial_until->isBefore(Carbon::now())) {
            return Auth::user()->redirectToCakeWithTemporaryLogin();
        }

        return $next($request);
    }
}
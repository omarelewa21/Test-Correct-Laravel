<?php

namespace tcCore\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ValidGeneralTerms
{
    public function handle(Request $request, Closure $next)
    {
        if (Auth::user()->hasNeedsToAcceptGeneralTerms()) {
            return Auth::user()->redirectToCakeWithTemporaryLogin();
        }

        return $next($request);
    }
}
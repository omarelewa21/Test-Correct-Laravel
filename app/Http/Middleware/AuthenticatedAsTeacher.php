<?php

namespace tcCore\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use Livewire\Livewire;
use tcCore\Http\Helpers\BaseHelper;

class AuthenticatedAsTeacher
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        if (optional(Auth::user())->isA('Teacher')){
            return $next($request);
        }
        /** @TODO should redirect to a dashboard page, but this is currently not available. */
        if (Livewire::isLivewireRequest()) {
            return abort(401,'Unauthorized');
        }
        return redirect(BaseHelper::getLoginUrl());
    }
}

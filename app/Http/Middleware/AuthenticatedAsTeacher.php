<?php

namespace tcCore\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

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
        if (optional(Auth::user())->isA('teacher')){
            return $next($request);
        }
        /** @TODO should redirect to a dashboard page, but this is currently not available. */
        return redirect(config('app.url_login'));
    }
}

<?php

namespace tcCore\Http\Middleware;

use Closure;

class GuestChoice
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
        if($request->query('take') === session()->get('guest_take')) {
            return $next($request);
        }

        return redirect(route('auth.login'));
    }
}

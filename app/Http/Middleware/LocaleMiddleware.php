<?php

namespace tcCore\Http\Middleware;

use Closure;

class LocaleMiddleware
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
        if ($request->has('locale')) {
            if (in_array(strtolower($request->getLocale()), ['en', 'nl'])) {
                session()->put('locale', $request->getLocale());
            } else {
                session()->put('locale', 'nl');
            }
        }
        if (session()->has('locale')) {
            app()->setLocale(session('locale'));
        }
        return $next($request);
    }
}

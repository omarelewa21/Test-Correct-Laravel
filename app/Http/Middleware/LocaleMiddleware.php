<?php

namespace tcCore\Http\Middleware;

use Closure;
use tcCore\Http\Helpers\BaseHelper;

class LocaleMiddleware
{

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $locale = auth()->user()?->getActiveLanguage() ?? BaseHelper::browserLanguage();

        session()->put('locale', $locale);
        app()->setLocale($locale);

        return $next($request);
    }
}

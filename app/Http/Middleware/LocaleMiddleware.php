<?php

namespace tcCore\Http\Middleware;

use Closure;

class LocaleMiddleware
{
    /**
     * Helper function - Getting browser language
     *
     * @return browser language
     */
    private function browserLanguage()
    {
        if (array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)) {
            return substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        } else {
            return 'nl';
        }
    }

    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $locale = $this->browserLanguage();
        if (auth()->user()->schoolLocation->school_language ?? false) {
            $locale = auth()->user()->schoolLocation->school_language;
        }

        // als je de browser op spaans of iets anders hebt staan valt hij nu naar Nederlands;
        if (!in_array(strtolower($locale), ['en', 'nl'])) {
            $locale = 'nl';
        }
        session()->put('locale', $locale);
        app()->setLocale($locale);

        return $next($request);
    }
}

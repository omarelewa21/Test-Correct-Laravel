<?php

namespace tcCore\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;

class LocaleMiddleware
{
    /**
     * Helper function - Getting browser language
     *
     * @return browser language
     */
    private function browserLanguage(){
        if(array_key_exists('HTTP_ACCEPT_LANGUAGE', $_SERVER)){
            return substr($_SERVER['HTTP_ACCEPT_LANGUAGE'], 0, 2);
        }else{
            return 'nl';
        }
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $locale = $this->browserLanguage();
        if ($user = Auth::user()){
            $locale = optional($user->schoolLocation)->school_language ?? $locale;
        }

        if (!in_array(strtolower($locale), ['en', 'nl'])) {
            $locale = 'nl';
        }
        session()->put('locale',$locale);

//        if ($request->has('locale')) {
//            if (in_array(strtolower($request->getLocale()), ['en', 'nl'])) {
//                session()->put('locale', $request->getLocale());
//            } else {
//                session()->put('locale', 'nl');
//            }
//        }

        if (session()->has('locale')) {
            app()->setLocale(session('locale'));
        }

        return $next($request);
    }
}

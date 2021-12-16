<?php

namespace tcCore\Http\Middleware;

use Closure;
use Illuminate\Support\Facades\Auth;
use tcCore\AppVersionInfo;
use tcCore\TemporaryLogin;

class AuthenticateWithTemporaryLogin
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
        if ($result = TemporaryLogin::isValid($request->temporary_login)) {
            if (Auth::loginUsingId($result['user'])) {
                $this->handleTemporaryLoginOptions($result['options']);
                session()->put('session_hash', auth()->user()->getAttribute('session_hash'));
                return $next($request);
            }
        }

        return redirect(config('app.url_login'));
    }

    private function handleTemporaryLoginOptions($options)
    {
        if (!$options) {
            return;
        }
        $options = json_decode($options);

        if (property_exists($options, 'app_details')) {
            $this->registerAppDetails($options);
            AppVersionInfo::createFromSession();
        }
    }

    private function registerAppDetails($options)
    {
        collect($options->app_details)->each(function ($detail, $key) {
            session()->put($key, $detail);
        });
    }
}

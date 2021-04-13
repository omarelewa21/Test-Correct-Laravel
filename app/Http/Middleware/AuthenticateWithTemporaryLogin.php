<?php

namespace tcCore\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use tcCore\TemporaryLogin;
use tcCore\User;

class AuthenticateWithTemporaryLogin
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
        if ($user = TemporaryLogin::isValid($request->temporary_login)) {
            if (Auth::loginUsingId($user)) {
                session()->put('session_hash', auth()->user()->getAttribute('session_hash'));
                return $next($request);
            }
        }

        return redirect(config('app.url_login'));
    }
}

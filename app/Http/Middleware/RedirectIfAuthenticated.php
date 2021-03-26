<?php namespace tcCore\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\RedirectResponse;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

use tcCore\TemporaryLogin;

class RedirectIfAuthenticated {

	/**
	 * The Guard implementation.
	 *
	 * @var Guard
	 */
	protected $auth;

	/**
	 * Create a new filter instance.
	 *
	 * @param  Guard  $auth
	 * @return void
	 */
	public function __construct(Guard $auth)
	{
		$this->auth = $auth;
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
	    if($request->temporary_login) {
            $user = optional(TemporaryLogin::whereUuid($request->temporary_login)->first())->user_id;

            if (Auth::loginUsingId($user)) {
                session()->put('session_hash',$this->auth->user()->getAttribute('session_hash'));


            }
	    }

		return $next($request);
	}

}

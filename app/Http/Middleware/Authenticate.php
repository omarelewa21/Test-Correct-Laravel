<?php namespace tcCore\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\UnauthorizedException;
use Livewire\Livewire;
use tcCore\Http\Helpers\BaseHelper;

class Authenticate {

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
		if ($this->auth->guest())
		{
            if (Livewire::isLivewireRequest()) {
                return abort(401,'Unauthorized');
            }

            if (! $request->expectsJson()) {
                return redirect()->away(BaseHelper::getLoginUrl());
            }
			return response('Unauthorized.', 401);
		}

		return $next($request);
	}

}

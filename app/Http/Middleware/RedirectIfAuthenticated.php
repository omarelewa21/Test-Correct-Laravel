<?php namespace tcCore\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use tcCore\Shortcode;
use tcCore\TestParticipant;
use tcCore\TestTake;

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
	    if($request->short_code) {
            $user = Shortcode::whereCode($request->short_code)->first()->user_id;

            if (Auth::loginUsingId($user)) {
                session()->put('session_hash',$this->auth->user()->getAttribute('session_hash'));
                return new RedirectResponse(url(route('student.test-take-laravel', $request->test_take->uuid)));
            }
	    }

		return $next($request);
	}

}

<?php namespace tcCore\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\RedirectResponse;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use tcCore\Shortcode;

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

                if ('auth.teacher.show-test-with-short-code' === Route::current()->getName() && Auth::user()->isA('Teacher')) {
                    return new RedirectResponse(url(route('test-preview', [$request->test->uuid, Auth::user()])));
                }

                if ('auth.login_test_take_with_short_code' === Route::current()->getName() && Auth::user()->isA('Student')) {
                    return new RedirectResponse(url(route('student.test-take-laravel', $request->test_take->uuid)));
                }
            }
	    }

		return $next($request);
	}

}

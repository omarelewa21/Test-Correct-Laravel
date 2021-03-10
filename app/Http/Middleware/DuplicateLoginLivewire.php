<?php namespace tcCore\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Contracts\Auth\Guard;
use tcCore\Lib\User\Roles;
use tcCore\User;

class DuplicateLoginLivewire {
    const DEBOUNCE = '30 seconds';

    function __construct(User $user, Guard $auth)
    {
        $this->user = $user;
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
        $roles = Roles::getUserRoles();

        if($this->shouldCheckSessionHash() && count($roles) === 1 && in_array('Student', $roles) && $this->auth->user()->getAttribute('session_hash') !== session('session_hash')) {
            session()->put('new_debounce_time', Carbon::now());
            return \Response::make("Session expired.", 440);
        }

        return $next($request);
    }

    private function shouldCheckSessionHash()
    {
        return Carbon::parse(session('new_debounce_time'), Carbon::now()->subMinute())->add(self::DEBOUNCE)->diffInSeconds(Carbon::now()) > 0;
    }
}


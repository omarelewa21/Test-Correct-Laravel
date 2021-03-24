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
        if ($this->auth->user()) {
            if ($this->shouldCheckSessionHash() && count($roles) === 1 && in_array('Student', $roles) && $this->auth->user()->getAttribute('session_hash') !== session('session_hash')) {
                session()->put('new_debounce_time', Carbon::now());

                if (strpos($request->getRequestUri(), 'livewire') !== false) {
                    return response()->make('Duplicate login', 440);
                }
                return redirect()->to(config('app.url_login'));
            }
        }

        return $next($request);
    }

    private function shouldCheckSessionHash()
    {
        if (session()->has('new_debounce_time')) {
            return Carbon::parse(session('new_debounce_time'))->add(self::DEBOUNCE)->diffInSeconds(Carbon::now()) > 0;
        }
        // always check if no new_debounce_time in session;
        return true;
    }
}


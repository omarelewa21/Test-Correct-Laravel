<?php namespace tcCore\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Livewire\Livewire;
use tcCore\Lib\User\Roles;
use tcCore\User;

class DuplicateLogin {

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

        if(count($roles) === 1 && in_array('Student', $roles) && $this->auth->user()->getAttribute('session_hash') !== $request->get('session_hash')) {
            if (Livewire::isLivewireRequest()) {
                return abort(440,'Session expirted');
            }
            return \Response::make("Session expired.", 440);
        }

        return $next($request);
    }
}


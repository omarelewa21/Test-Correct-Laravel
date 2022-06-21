<?php

namespace tcCore\Factories\Traits;

use Illuminate\Support\Facades\Auth;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\User;

trait DoWhileLoggedInTrait
{
    /**
     * Login with the supplied user model, execute code in callback, finaly logout
     *
     * @param callable $callback callback return value gets returned by doWhileLoggedIn Method
     * @param User $user
     * @return mixed returns return value of callback
     */
    protected function doWhileLoggedIn(callable $callback, User $user)
    {
        Auth::login($user);
        ActingAsHelper::getInstance()->setUser($user);
        $return = $callback();
        Auth::logout();

        return $return;
    }
}
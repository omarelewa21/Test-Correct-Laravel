<?php namespace tcCore\Lib\User;

use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Http\Helpers\RequestCacheHelper;
use tcCore\User;

class Roles
{
    public static function getUserRoles(User|null $user = null)
    {
        $user ??= ActingAsHelper::getInstance()->getUser();

        if ($user === null) {
            return [];
        }
        return RequestCacheHelper::get($user->getKey(), function () use ($user) {
            return $user->roles
                ->map(fn($role) => $role->name)
                ->toArray();
        });
    }
}
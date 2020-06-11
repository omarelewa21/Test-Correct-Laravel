<?php namespace tcCore\Lib\User;

use Illuminate\Support\Facades\Auth;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\User;

class Roles {
    public static function getUserRoles(User $user = null) {
        if ($user === null) {
            $user = ActingAsHelper::getInstance()->getUser();
            if ($user !== null) {
                $userRoles = $user->roles;
            } else {
                return [];
            }
        } else {
            $userRoles = $user->roles;
        }

        $roles = [];
        foreach ($userRoles as $userRole) {
            $roles[] = $userRole['name'];
        }

        return $roles;
    }
}
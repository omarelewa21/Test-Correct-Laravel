<?php namespace tcCore\Lib\User;

use Illuminate\Support\Facades\Auth;
use tcCore\User;

class Roles {
    public static function getUserRoles(User $user = null) {
        if ($user === null) {
            $user = Auth::user();
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
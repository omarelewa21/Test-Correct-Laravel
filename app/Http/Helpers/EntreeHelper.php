<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 17/01/2019
 * Time: 13:33
 */

namespace tcCore\Http\Helpers;


use Illuminate\Support\Facades\Auth;
use tcCore\User;

class EntreeHelper
{

    public static function redirectIfBrinUnknown($external_main_code, $external_sub_code)
    {
        $location = SchoolLocation::where(['external_main_code', $external_main_code])
            ->where('external_sub_code', $external_sub_code)
            ->first();
        if ($location == null) {
            header("Location: $url");
            exit;
        }
    }

    public static function shouldPromptForEntree(User $user)
    {
        return ($user->schoolLocation->lvs_active && empty($user->eck_id));
    }

}

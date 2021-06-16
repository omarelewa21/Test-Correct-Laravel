<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 17/01/2019
 * Time: 13:33
 */

namespace tcCore\Http\Helpers;


use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use tcCore\SchoolLocation;
use tcCore\User;

class EntreeHelper
{

    public static function redirectIfBrinUnknown($brinZesCode=null)
    {
        $location = null;
        if (strlen($brinZesCode) === 6) {
            $external_main_code = substr($brinZesCode, 0, 4);
            $external_sub_code = substr($brinZesCode, 4, 2);

            $location = SchoolLocation::where('external_main_code', $external_main_code)
                ->where('external_sub_code', $external_sub_code)
                ->first();
        }
        if ($location == null) {
            $url =  route('auth.login', ['tab' => 'login', 'message_brin' => 'brin_not_found']);
            if (!App::runningUnitTests()) {
                header("Location: $url");
                exit;
            } else {
                return $url;
            }
        }
        return true;
    }

    public static function shouldPromptForEntree(User $user)
    {
        return (optional($user->schoolLocation)->lvs_active && empty($user->eck_id));
    }

    public static function getBrinZes($attr) {
        if (array_key_exists('nlEduPersonHomeOrganizationBranchId', $attr) && $attr['nlEduPersonHomeOrganizationBranchId'][0]) {
            return $attr['nlEduPersonHomeOrganizationBranchId'][0];
        }
        return null;
    }

}

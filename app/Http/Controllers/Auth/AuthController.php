<?php

namespace tcCore\Http\Controllers\Auth;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use tcCore\FailedLogin;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Http\Helpers\EntreeHelper;
use tcCore\Http\Helpers\UserHelper;
use tcCore\Lib\User\Roles;
use tcCore\LoginLog;
use tcCore\User;
use tcCore\Jobs\SetSchoolYearForDemoClassToCurrent;

class AuthController extends Controller
{

    function __construct(User $user, Guard $auth)
    {
        $this->user = $user;
        $this->auth = $auth;
    }

    public function doWeNeedCaptcha(Request $request)
    {
        if(FailedLogin::doWeNeedExtraSecurityLayer($request->get('username'))){
            return \Response::make(true, 200);
        }
        return \Response::make(false,200);
    }

    public function getApiKey(Request $request)
    {
        $user = $request->get('user');
        $password = $request->get('password');
        $captcha = $request->get('captcha');
        $ip = $request->get('ip');

        if(FailedLogin::doWeNeedExtraSecurityLayer($user) && !$captcha){
            return \Response::make("NEEDS_CAPTCHA", 403);
        }

        if ($this->auth->once(['username' => $user, 'password' => $password])) {
            $user = $this->auth->user();

            return (new UserHelper())->handleAfterLoginValidation($user,false, $ip);
        } else {
            FailedLogin::create([
               'username' => $user,
               'ip' => $ip
            ]);
            return \Response::make("Invalid credentials.", 403);
        }
    }


}

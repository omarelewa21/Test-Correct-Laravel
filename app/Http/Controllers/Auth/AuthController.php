<?php

namespace tcCore\Http\Controllers\Auth;

use Carbon\Carbon;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

use Illuminate\Support\Str;
use tcCore\FailedLogin;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Helpers\AppVersionDetector;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Http\Helpers\EntreeHelper;
use tcCore\Http\Helpers\UserHelper;
use tcCore\Lib\User\Roles;
use tcCore\LoginLog;
use tcCore\User;
use tcCore\Jobs\SetSchoolYearForDemoClassToCurrent;
use tcCore\GeneralTermsLog;

class AuthController extends Controller
{

    protected $text2speechPriceRoles = ['Teacher','Administrator','School manager','School management','Mentor'];

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

    public function getLaravelLoginPage(Request $request)
    {
        $baseUrl = config('app.base_url');
        $loginRoute = route('auth.login',[], false);

        if (Str::endsWith($baseUrl, '/')) {
            $baseUrl = Str::replaceLast('/', '', $baseUrl);
        }
        $url['url'] = sprintf('%s%s', $baseUrl, $loginRoute);

        return \Response::make($url);
    }

    public function getLaravelEntreeUrl(Request $request)
    {
        $baseUrl = config('app.base_url');
        $entreeRoute = route('redirect-to-entree');

        if (Str::endsWith($baseUrl, '/')) {
            $baseUrl = Str::replaceLast('/', '', $baseUrl);
        }
        $url['url'] = sprintf('%s%s', $baseUrl, $entreeRoute);

        return \Response::make($url);
    }
}

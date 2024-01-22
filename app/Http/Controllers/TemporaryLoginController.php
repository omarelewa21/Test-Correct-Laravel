<?php

namespace tcCore\Http\Controllers;


use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use tcCore\AppVersionInfo;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\TemporaryLogin;
use tcCore\User;

class TemporaryLoginController extends Controller
{

    /**
     * @param Request $request
     * @return RedirectResponse
     * User and data already set in the middleware AuthenticateWithTemporaryLogin
     */
    public function redirect(Request $request ){
        if(null !== Auth::user() && $request->has('redirect')){
            return new RedirectResponse($request->redirect);
        }
    }

    public function create(Request $request)
    {
        if($request->has('options')){
            if(is_array($request->get('options'))){
                $options = $request->get('options');
                $t = TemporaryLogin::createWithOptionsForUser(array_keys($options),array_values($options),Auth::user());
            }
        } else {
            $t = TemporaryLogin::createForUser(Auth::user());
        }

        $redirect = '/';
        if($request->has('redirect')){
            $redirect = $request->get('redirect');
        }
        return BaseHelper::createRedirectUrlWithTemporaryLoginUuid($t->uuid,$redirect);
    }

    public function toCake(Request $request)
    {
        if($request->has('options')){
            if(is_array($request->get('options'))){
                $options = $request->get('options');
                $t = TemporaryLogin::createWithOptionsForUser(array_keys($options),array_values($options),Auth::user());
            }
        } else {
            $t = TemporaryLogin::createForUser(Auth::user());
        }

        $redirect = '/';
        if($request->has('redirect')){
            $redirect = $request->get('redirect');
        }
        header('Location: '.$this->createRedirectUrlWithTemporaryLoginUuidToCake($t->uuid,$redirect));
        exit;
    }

    public function toCakeUrl(Request $request)
    {
        if($request->has('options')){
            if(is_array($request->get('options'))){
                $options = $request->get('options');
                $t = TemporaryLogin::createWithOptionsForUser(array_keys($options),array_values($options),Auth::user());
            }
        } else {
            $t = TemporaryLogin::createForUser(Auth::user());
        }

        $redirect = '/';
        if($request->has('redirect')){
            $redirect = $request->get('redirect');
        }
        return $this->createRedirectUrlWithTemporaryLoginUuidToCake($t->uuid,$redirect);
    }

    private function createRedirectUrlWithTemporaryLoginUuidToCake($uuid, $redirectUrl)
    {
        $response = new \stdClass;

        $relativeUrl = sprintf('%s?redirect=%s',
            sprintf('users/temporary_login/%s',$uuid),
            rawurlencode($redirectUrl)
        );
        if(Str::startsWith($relativeUrl,'/')) {
            $relativeUrl = Str::replaceFirst('/', '', $relativeUrl);
        }
        if(Auth::user()) {
            Auth::logout();
        }
        return sprintf('%s%s',BaseHelper::getLoginUrl(), $relativeUrl);
    }

}

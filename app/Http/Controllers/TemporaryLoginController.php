<?php

namespace tcCore\Http\Controllers;


use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use tcCore\AppVersionInfo;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\TemporaryLogin;
use tcCore\User;
use tcCore\UserSystemSetting;

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

    public function setSettingFromCake(Request $request)
    {
        if(!$request->has('setting')){
            return false;
        }

        if(!is_array($request->has('setting'))){
            return false;
        }

        UserSystemSetting::setSetting(Auth::user(),$request['title'], $request['value']);
        UserSystemSetting::getAll(Auth::user());
        return UserSystemSetting::getAll(Auth::user());
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
        header('Location: '.BaseHelper::createRedirectUrlWithTemporaryLoginUuidToCake($t->uuid,$redirect));
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
        return BaseHelper::createRedirectUrlWithTemporaryLoginUuidToCake($t->uuid,$redirect);
    }


}

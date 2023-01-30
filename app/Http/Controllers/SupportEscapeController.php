<?php

namespace tcCore\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use tcCore\Http\Helpers\CakeRedirectHelper;
use tcCore\SupportTakeOverLog;
use tcCore\User;

class SupportEscapeController extends Controller
{

    public function index(Request $request)
    {
        $supportId= session()->get('support.id');

        if (empty($supportId)){
            return abort(401,'Unauthorized');
        }

        Auth::logout();

        $user = User::whereUuid($supportId)->first();
        Auth::loginUsingId($user->id);
        session()->forget('support');
        return CakeRedirectHelper::redirectToCake('dashboard',$user);
    }
}

<?php

namespace tcCore\Http\Controllers;


use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        return TemporaryLogin::createForUser(Auth::user());
    }

}

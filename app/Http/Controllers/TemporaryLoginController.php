<?php

namespace tcCore\Http\Controllers;


use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TemporaryLoginController extends Controller
{

    public function __invoke(Request $request ){
        if(null !== Auth::user() && $request->has('redirect')){
            return new RedirectResponse($request->redirect);
        }
    }

}

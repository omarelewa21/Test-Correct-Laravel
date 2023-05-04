<?php
namespace tcCore\Http\Controllers;

use tcCore\Http\Helpers\CakeRedirectHelper;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->user()?->isA('student')){
            return redirect()->route('student.dashboard');
        }

        if (auth()->user()?->isA('teacher')) {
            return CakeRedirectHelper::redirectToCake();
        }

        return redirect()->route('auth.login');
    }
}
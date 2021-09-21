<?php

namespace tcCore\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use tcCore\SupportTakeOverLog;
use tcCore\User;

class SupportTakeOverLogController extends Controller
{
    public function store(User $user)
    {
        return Response::make(
            SupportTakeOverLog::createForUserWithSupportUserAndLocation($user, Auth::user(), \Request::ip())
        );
    }
}

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

    public function show(User $user)
    {
        $logs = $user->supportTakeOverLogs()
            ->with(
                'user:id,name,name_suffix,name_first',
                'user.roles:id,name'
            )
            ->orderBy('created_at', 'desc')
            ->get();

        return Response::make($logs, 200);
    }

    public function index()
    {
        $logs = SupportTakeOverLog::with('user:id,name,name_suffix,name_first', 'user.roles:id,name', 'supportUser:id,name,name_suffix,name_first')
            ->orderBy('created_at', 'desc')
            ->get();

        $logs->each(function($log) {
            $log->user->setAttribute('fullname', $log->user->getNameFullAttribute());
            $log->supportUser->setAttribute('fullname', $log->supportUser->getNameFullAttribute());
        });

        return Response::make($logs, 200);
    }
}

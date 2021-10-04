<?php

namespace tcCore\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
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

    public function index(Request $request)
    {
        $logs = SupportTakeOverLog::with('user:id,name,name_suffix,name_first', 'user.roles:id,name', 'supportUser:id,name,name_suffix,name_first')
            ->when(array_key_exists('created_at',$request->get('order')), function ($query) use ($request) {
                collect($request->get('order'))->each(function($order, $key) use ($query) {
                    $query->orderBy($key, $order);
                });
            }, function($query) {
                $query->latest();
            })
            ->paginate(15);

        foreach ($logs->items() as $log) {
            $log->user->setAttribute('fullname', $log->user->getNameFullAttribute());
            $log->supportUser->setAttribute('fullname', $log->supportUser->getNameFullAttribute());
        };

        return Response::make($logs, 200);
    }
}

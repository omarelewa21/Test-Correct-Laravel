<?php

namespace tcCore\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Request;
use tcCore\SupportTakeOverLog;
use tcCore\User;

class SupportTakeOverLogController extends Controller
{
    public function store(User $user, Request $request)
    {
        return Response::make(
            SupportTakeOverLog::createForUserWithSupportUserAndIp($user, Auth::user(), $request->get('ip'))
        );
    }

    public function show(User $user)
    {
        $logs = $user->supportTakeOverLogs()
            ->with(
                'user:id,name,name_suffix,name_first,school_location_id',
                'user.roles:id,name',
                'user.schoolLocation:id,name'
            )
            ->latest()
            ->get();

        return Response::make($logs, 200);
    }

    public function index(Request $request)
    {
        $logs = SupportTakeOverLog::with('user:id,name,name_suffix,name_first,school_location_id', 'user.roles:id,name', 'user.schoolLocation:id,name','supportUser:id,name,name_suffix,name_first')
            ->when(array_key_exists('created_at',$request->get('order')), function ($query) use ($request) {
                collect($request->get('order'))->each(function($order, $key) use ($query) {
                    $query->orderBy($key, $order);
                });
            }, function($query) {
                $query->latest();
            })
            ->paginate(15);

        foreach ($logs->items() as $log) {
            if(!$log->user){
                $log->user = User::withTrashed()->find($logs->user_id);
                if($log->user) {
                    $log->user->name .= '(deleted)';
                } else {
                    $log->user = User::make([
                        'username' => 'deleted user',
                        'fullname' => 'deleted user',
                    ]);
                }
            }
            $log->user->setAttribute('fullname', $log->user->getNameFullAttribute());

            if(!$log->supportUser){
                $log->supportUser = User::withTrashed()->find($logs->support_user_id);
                if($log->supportUser){
                    $log->supportUser->name .= '(deleted)';
                } else {
                    $log->supportUser = User::make([
                        'username' => 'deleted support user',
                        'fullname' => 'deleted support user',
                    ]);
                }
            }
            $log->supportUser->setAttribute('fullname', $log->supportUser->getNameFullAttribute());
        }

        return Response::make($logs, 200);
    }
}

<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use tcCore\Deployment;
use tcCore\Http\Requests\CreateDeploymentRequest;
use tcCore\Http\Requests\CreateInfoRequest;
use tcCore\Http\Requests\DeleteDeploymentRequest;
use tcCore\Http\Requests\DeleteInfoRequest;
use tcCore\Http\Requests\IndexDeploymentRequest;
use tcCore\Http\Requests\IndexInfoRequest;
use tcCore\Http\Requests\ShowDeploymentRequest;
use tcCore\Http\Requests\ShowInfoRequest;
use tcCore\Http\Requests\UpdateDeploymentRequest;
use tcCore\Http\Requests\UpdateInfoRequest;
use tcCore\Info;
use tcCore\UserInfosDontShow;

class InfoController extends Controller
{

    public function index(IndexInfoRequest $request)
    {
        $data = null;
        switch($request->mode){
            case 'index':
                $data = Info::orderBy('show_from','desc')->with('roles')->get();
                break;
            case 'dashboard':
                $data = Info::getInfoForUser(Auth::user(), true);
                break;
            default:
                $data = Info::getInfoForUser(Auth::user());
        }

        return Response::make($data, 200);
    }

    public function show(ShowInfoRequest $request, Info $info)
    {
        return Response::make($info->load('roles'), 200);
    }

    public function update(UpdateInfoRequest $request, Info $info)
    {
        $info->fill($request->validated());
        $info->save();
        $info->saveRoleInfo($request->validated('roles'));
        return Response::make($info,200);
    }

    public function store(CreateInfoRequest $request)
    {

        $info = Info::create($request->validated());
        $info->saveRoleInfo($request->validated('roles'));
        return Response::make($info,200);
    }

    public function delete(DeleteInfoRequest $request, Info $info)
    {
        $info->delete();
        return Response::make(true,200);
    }

    public function removeDashboardInfo(Info $info){
        if(!auth()->user()->isA('student')){
            UserInfosDontShow::create([
                'user_id'       => auth()->id(),
                'info_id'       => $info->id
            ]);
            return Response::make(true,200);
        }

        return Response::make(false, 500);

    }

}

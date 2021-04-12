<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Deployment;
use tcCore\Http\Requests\CreateDeploymentRequest;
use tcCore\Http\Requests\CreateMaintenanceWhitelistIpRequest;
use tcCore\Http\Requests\DeleteDeploymentRequest;
use tcCore\Http\Requests\DeleteMaintenanceWhitelistIpRequest;
use tcCore\Http\Requests\IndexDeploymentRequest;
use tcCore\Http\Requests\IndexMaintenanceWhitelistIpRequest;
use tcCore\Http\Requests\ShowDeploymentRequest;
use tcCore\Http\Requests\ShowMaintenanceWhitelistIpRequest;
use tcCore\Http\Requests\UpdateDeploymentRequest;
use tcCore\Http\Requests\UpdateMaintenanceWhitelistIpRequest;
use tcCore\MaintenanceWhitelistIp;

class MaintenanceWhitelistIpController extends Controller
{
    public function index(IndexMaintenanceWhitelistIpRequest $request)
    {
        return Response::make(Deployment::orderBy('deployment_day','desc')->get(), 200);
    }

    public function show(ShowMaintenanceWhitelistIpRequest $request, MaintenanceWhitelistIp $maintenanceWhitelistIp)
    {
        return Response::make($maintenanceWhitelistIp, 200);
    }

    public function update(UpdateMaintenanceWhitelistIpRequest $request, MaintenanceWhitelistIp $maintenanceWhitelistIp)
    {
        $maintenanceWhitelistIp->fill($request->validated());
        $maintenanceWhitelistIp->save();
        return Response::make($maintenanceWhitelistIp,200);
    }

    public function create(CreateMaintenanceWhitelistIpRequest $request)
    {
        $maintenanceWhitelistIp = MaintenanceWhitelistIp::create($request->validated());
        return Response::make($maintenanceWhitelistIp,200);
    }

    public function delete(DeleteMaintenanceWhitelistIpRequest $request, MaintenanceWhitelistIp $maintenanceWhitelistIp)
    {
        $maintenanceWhitelistIp->delete();
        return Response::make(true,200);
    }
}

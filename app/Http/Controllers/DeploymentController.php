<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Deployment;
use tcCore\Http\Requests\CreateDeploymentRequest;
use tcCore\Http\Requests\DeleteDeploymentRequest;
use tcCore\Http\Requests\IndexDeploymentRequest;
use tcCore\Http\Requests\ShowDeploymentRequest;
use tcCore\Http\Requests\UpdateDeploymentRequest;

class DeploymentController extends Controller
{

    public function index(IndexDeploymentRequest $request)
    {
        return Response::make(Deployment::orderBy('deployment_day','desc')->get(), 200);
    }

    public function show(ShowDeploymentRequest $request, Deployment $deployment)
    {
        return Response::make($deployment, 200);
    }

    public function update(UpdateDeploymentRequest $request, Deployment $deployment)
    {
        $oldStatus = $deployment->status;
        $deployment->fill($request->validated());
        $deployment->save();
        $deployment->handleIfNeeded($oldStatus);
        return Response::make($deployment,200);
    }

    public function create(CreateDeploymentRequest $request)
    {
        $deployment = Deployment::create($request->validated());
        $deployment->handleIfNeeded(null);
        return Response::make($deployment,200);
    }

    public function delete(DeleteDeploymentRequest $request, Deployment $deployment)
    {
        $deployment->delete();
        $deployment->handleIfNeeded(null);
        return Response::make(true,200);
    }
}

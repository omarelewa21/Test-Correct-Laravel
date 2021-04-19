<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Deployment;
use tcCore\Http\Requests\CreateDeploymentRequest;
use tcCore\Http\Requests\DeleteDeploymentRequest;
use tcCore\Http\Requests\IndexDeploymentRequest;
use tcCore\Http\Requests\ShowDeploymentMaintenanceRequest;
use tcCore\Http\Requests\ShowDeploymentRequest;
use tcCore\Http\Requests\UpdateDeploymentRequest;
use tcCore\MaintenanceWhitelistIp;

class DeploymentMaintenanceController extends Controller
{

    public function checkForMaintenance(Request $request)
    {
        $deployment = Deployment::whereIn('status',[Deployment::ACTIVE,Deployment::NOTIFY])->orderBy('status','asc')->first();
//        logger('DeploymentMaintenance');
        if(null !== $deployment){
//            logger('DeploymentMaintenance: we do have a deployment with active or notify '.$deployment->status);
            if($deployment->status === Deployment::ACTIVE) {
//                logger('DeploymentStatus: status is active');
                $ips = MaintenanceWhitelistIp::pluck('ip');
                return response::make([
                    'status' => $deployment->status,
                    'message' => $deployment->content,
                    'notification' => $deployment->notification,
                    'whitelisted_ips' => $ips
                ],200);
            } else {
//                logger('DeploymentStatus: status is notify');
                return response::make([
                    'status' => $deployment->status,
                    'notification' => $deployment->notification,
                ],200);
            }
        }
//        logger('DeploymentMaintenance: nothing to do');
        return Response::make(false, 200);
    }

}

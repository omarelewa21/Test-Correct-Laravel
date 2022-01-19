<?php

namespace tcCore\Http\Middleware;

use Closure;
use Symfony\Component\HttpFoundation\IpUtils;
use tcCore\Deployment;
use tcCore\Exceptions\DeploymentMaintenanceException;
use tcCore\Http\Helpers\GlobalStateHelper;
use tcCore\MaintenanceWhitelistIp;

class CheckForDeploymentMaintenance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $deployment = Deployment::whereStatus(Deployment::ACTIVE)->first();
        if($deployment) {
            GlobalStateHelper::getInstance()->setHasActiveMaintenance(true);
            $ips = MaintenanceWhitelistIp::pluck('ip');
            if ($ips && $ips->count() > 0 && IpUtils::checkIp($request->ip(), $ips->toArray())) {
                return $next($request);
            }
            throw new DeploymentMaintenanceException($deployment);
        }

        return $next($request);
    }
}

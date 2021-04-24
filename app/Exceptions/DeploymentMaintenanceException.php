<?php

namespace tcCore\Exceptions;

use Exception;
use tcCore\Deployment;

class DeploymentMaintenanceException extends Exception
{
    public $deployment;

    public function __construct(Deployment $deployment)
    {
        $this->deployment = $deployment;
    }
}

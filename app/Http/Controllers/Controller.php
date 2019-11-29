<?php namespace tcCore\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use tcCore\Lib\User\Roles;
use tcCore\TestParticipant;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    protected function getUserRoles()
    {
        return Roles::getUserRoles();
    }

    protected function getAlertStatusOrParticipant(TestParticipant $testParticipant)
    {
        $alert = false;

        foreach($testParticipant->testTakeEvents as $testTakeEvent) {
            if ($testTakeEvent->testTakeEventType->requires_confirming == 1 && $testTakeEvent->confirmed == 0) {
                $alert = true;
                break;
            }
        }
        return $alert;
    }

}

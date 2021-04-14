<?php

namespace tcCore;

use Dyrynda\Database\Casts\EfficientUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Artisan;
use tcCore\Traits\UuidTrait;

class Deployment extends Model
{
    use SoftDeletes;
    use UuidTrait;

    public const PLANNED = 'PLANNED';
    public const NOTIFY = 'NOTIFY';
    public const ACTIVE = 'ACTIVE';
    public const DONE = 'DONE';

    protected $casts = [
        'uuid' => EfficientUuid::class,
        'deployment_day' => 'datetime:Y-m-d',
    ];

    protected $fillable = [
      'content',
      'notification',
      'deployment_day',
      'status'
    ];

    public function handleIfNeeded($oldStatus) : void
    {
        logger('Deployment: handle if needed');
        if($oldStatus !== $this->status){
            logger('Deployment: status changed to '.$this->status);
            if($this->status === static::ACTIVE){
                logger('Deployment: show maintenance should be set');
                $this->showMaintenance();
            }
            else if($oldStatus === static::ACTIVE){
                logger('Deployment: remove maintenance should be set');
                $this->removeMaintenance();
            }
            logger('Deployment: do a portal call in order to set the notification');
            $this->callCakeForMaintenanceCheck();
        }
    }

    public function showMaintenance()
    {
        logger('Deployment: ready to set mainteanance');
        // nothing to do on the laravel side as this is based on an ACTIVE status
    }

    protected function removeMaintenance()
    {
        logger('Deployment: ready to remove maintenance');
        // nothing to do on the laravel side as this is based on an ACTIVE status or not
    }

    protected function callCakeForMaintenanceCheck()
    {
        logger('Deployment: ready to call Cake');
        $url = sprintf('%sdeployment_maintenance/check_for_maintenance',config('app.url_login'));
        logger('Deployment: url to call '.$url);
        $response = file_get_contents($url);
        logger('Deployment: response from url ');
        logger($response);
    }
}

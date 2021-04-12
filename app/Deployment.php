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
        'deployment_day' => 'datetime',
    ];

    protected $fillable = [
      'content',
      'notification',
      'deployment_day',
      'status'
    ];

    public function handleIfNeeded() : void
    {
        if($this->isDirty('status')){
            if($this->status === static::DONE && $this->getOriginal('status') === static::ACTIVE){
                $this->showMaintenance();
            }
            else if($this->status === static::ACTIVE){
                $this->removeMaintenance();
            }
        }
    }

    public function showMaintenance()
    {
        // set system in maintenance mode
        $ips = MaintenanceWhitelistIp::pluck('ip');
        $callString = sprintf('down %s --message="%s"',
            implode(' --alllow=',$ips),
            $this->content
        );
        Artisan::call($callString);
        // call cake to check if in maintenance mode
        $this->callCakeForMaintenanceCheck();
    }

    protected function removeMaintenance()
    {
        // remote system maintenance mode
        Artisan::call('up');
        // call cake to check if in maintenance mode
        $this->callCakeForMaintenanceCheck();
    }

    protected function callCakeForMaintenanceCheck()
    {

    }
}

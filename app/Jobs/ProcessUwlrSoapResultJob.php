<?php

namespace tcCore\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use tcCore\Http\Helpers\ImportHelper;
use tcCore\SchoolLocation;
use tcCore\UmbrellaOrganization;
use tcCore\User;
use tcCore\UwlrSoapResult;

class ProcessUwlrSoapResultJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    /**
     * @var UmbrellaOrganization
     */
    protected $uwlrSoapResultId;

    public $timeout = 1800; // 30 minutes
    public $tries = 1;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($uwlrSoapResultId)
    {
        //
        $this->uwlrSoapResultId = $uwlrSoapResultId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        set_time_limit(0);
        $resultSet = UwlrSoapResult::find($this->uwlrSoapResultId);
        if(!$resultSet){
            throw new \Exception('we could not find the corresponding resultset  with id '.$this->uwlrSoapResultId);
        }
        if($resultSet->status === 'PROCESSING'){
            throw new \Exception('trying to process the resultset while already running, resultset  with id '.$this->uwlrSoapResultId);
        }
        $accountManager = User::leftJoin('user_roles','user_roles.user_id','users.id')->where('user_roles.role_id',5)->first();
        Auth::loginUsingId($accountManager->getKey());

        $resultSet->status = 'PROCESSING';
        $resultSet->save();
        $helper = ImportHelper::initWithUwlrSoapResult(
            $resultSet,
            'sobit.nl'
        );

        $result = $helper->process();
        $resultSet->status = 'DONE';
        $resultSet->save();

    }
}

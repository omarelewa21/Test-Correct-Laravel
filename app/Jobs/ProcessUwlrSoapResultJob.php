<?php

namespace tcCore\Jobs;

use Carbon\Carbon;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use tcCore\Http\Helpers\ImportHelper;
use tcCore\Http\Helpers\UwlrImportHelper;
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

    public $timeout = 7200; // 60 minutes
    public $tries = 1;
    public $queue = 'import';
    public $autoNext = false;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($uwlrSoapResultId, $autoNext = false)
    {
        //
        $this->uwlrSoapResultId = $uwlrSoapResultId;
        $this->autoNext = $autoNext;
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
            // should be a logger notice but let's do an exception for the moment so that we can see what happens in bugsnag
            throw new \Exception('we could not find the corresponding resultset  with id '.$this->uwlrSoapResultId);
        }
        if($resultSet->status !== 'READYTOPROCESS'){
            // should be a logger notice but let's do an exception for the moment so that we can see what happens in bugsnag
            logger('trying to process the resultset with the wrong status '.$resultSet->status.', resultset  with id '.$this->uwlrSoapResultId);
            return true;
        }

        $resultSet->addToLog('jobFromQueue',Carbon::now())->addQueueDataToLog('jobsAtFromQueue',true);


        $accountManager = User::leftJoin('user_roles','user_roles.user_id','users.id')->where('user_roles.role_id',5)->first();
        Auth::loginUsingId($accountManager->getKey());

        $resultSet->status = 'PROCESSING';
        $resultSet->save();

        SchoolLocation::where('external_main_code',$resultSet->brin_code)
            ->where('external_sub_code',$resultSet->dependance_code)
            ->update(['auto_uwlr_import_status' => UwlrImportHelper::AUTO_UWLR_IMPORT_STATUS_PROCESSING]);

        try {
            $helper = ImportHelper::initWithUwlrSoapResult(
                $resultSet,
                'sobit.nl'
            );

            $result = $helper->process();
            $resultSet->status = 'DONE';
            $resultSet->addToLog('jobFinished', Carbon::now());
            $resultSet->save();
            SchoolLocation::where('external_main_code',$resultSet->brin_code)
                ->where('external_sub_code',$resultSet->dependance_code)
                ->update([
                    'auto_uwlr_import_status' => UwlrImportHelper::AUTO_UWLR_IMPORT_STATUS_DONE,
                    'auto_uwlr_last_import' => Carbon::now(),
                ]);
            // send notification to support about importing
            $schoolLocationName = SchoolLocation::where('external_main_code',$resultSet->brin_code)
                ->where('external_sub_code',$resultSet->dependance_code)
                ->value('name');

            SendUwlrImportSchoolLocationSuccessToSupportJob::dispatch($schoolLocationName);

        }
        catch (\Throwable $e){
            SchoolLocation::where('external_main_code',$resultSet->brin_code)
                ->where('external_sub_code',$resultSet->dependance_code)
                ->update(['auto_uwlr_import_status' => UwlrImportHelper::AUTO_UWLR_IMPORT_STATUS_FAILED]);
            throw $e;

        }

        if($this->autoNext){
            UwlrImportHelper::handleIfMoreSchoolLocationsCanBeImported();
        }

    }
}

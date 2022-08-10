<?php

namespace tcCore\Jobs;

use Facebook\WebDriver\Exception\ElementClickInterceptedException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use tcCore\Exceptions\SchoolAndSchoolLocationsImportException;
use tcCore\Http\Helpers\GlobalStateHelper;
use tcCore\Http\Helpers\SchoolImportHelper;
use tcCore\SchoolLocation;
use tcCore\User;

class UpdateSchoolLocationFromImport implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $user;
    protected $row;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($row, User $user)
    {
        $this->row = $row;
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::beginTransaction();
        try {
            $schoolLocation = SchoolLocation::where('customer_code',$this->row['customer_code'])->first();
            GlobalStateHelper::getInstance()->setQueueAllowed(false);
            GlobalStateHelper::getInstance()->setPreventDemoEnvironmentCreationForSchoolLocation(true);
            $helper = new SchoolImportHelper();
            $helper->updateSchoolLocationFromImport($schoolLocation,$this->row);
            GlobalStateHelper::getInstance()->setPreventDemoEnvironmentCreationForSchoolLocation(false);
            GlobalStateHelper::getInstance()->setQueueAllowed(true);
            DB::commit();
        }catch(\Throwable $e){
            DB::rollBack();
            if($e instanceof SchoolAndSchoolLocationsImportException){
                throw $e;
            } else {
                throw new SchoolAndSchoolLocationsImportException('Error on update school location from import', $e->getCode(), $e);
            }
        }
    }
}

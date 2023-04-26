<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use tcCore\GeneralTermsLog;
use tcCore\SchoolLocation;
use tcCore\SchoolLocationUser;
use tcCore\TrialPeriod;

class ClientLicenseDeleteTrialAndTermsRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schoollocation:deleteTrialAndTermsRecordsClientLicense';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete the trial period and general terms records for school locations where the license type is changed to client';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        TrialPeriod::whereIn('school_location_id',SchoolLocation::where('license_type','CLIENT')->select('id'))->delete();
        $schoolLocationIdsBuilder = SchoolLocation::where('license_type','CLIENT')->select('id');
        $userIdsBuilder = SchoolLocationUser::whereIn('school_location_id',$schoolLocationIdsBuilder)->select('user_id');
        GeneralTermsLog::whereIn('user_id',$userIdsBuilder)->delete();
        return Command::SUCCESS;
    }
}

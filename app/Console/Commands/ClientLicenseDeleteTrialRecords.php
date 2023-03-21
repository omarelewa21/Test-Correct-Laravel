<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use tcCore\SchoolLocation;
use tcCore\TrialPeriod;

class ClientLicenseDeleteTrialRecords extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'schoollocation:deleteTrialRecordsClientLicense';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete the trial period records for school locations where the license type is changed to client';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        TrialPeriod::whereIn('school_location_id',SchoolLocation::where('license_type','CLIENT')->select('id'))->delete();
        return Command::SUCCESS;
    }
}

<?php

namespace tcCore\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use tcCore\Jobs\CountAccountManagerLicenses;
use tcCore\License;
use tcCore\SchoolLocation;

class StartLicenseJobs extends Command
{
    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:license';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedules jobs to update license counters.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $date = new Carbon();

        $schoolLocations = SchoolLocation::whereIn('id', function($query) use ($date) {
            $license = new License();
            $query->select('school_location_id')->from($license->getTable())->where('start', $date);
        });

        foreach($schoolLocations as $schoolLocation) {
            $this->info('Licenses started for '.$schoolLocation->getKey());
            $this->dispatch(new CountAccountManagerLicenses($schoolLocation));
        }

        $date->subDays(1);
        $schoolLocations = SchoolLocation::whereIn('id', function($query) use ($date) {
            $license = new License();
            $query->select('school_location_id')->from($license->getTable())->where('end', $date);
        });

        foreach($schoolLocations as $schoolLocation) {
            $this->info('Licenses expired for '.$schoolLocation->getKey());
            $this->dispatch(new CountAccountManagerLicenses($schoolLocation));
        }
    }
}

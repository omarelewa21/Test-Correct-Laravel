<?php

namespace tcCore\Jobs;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use tcCore\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\SchoolLocation;

class CountSchoolLocationExpiredLicenses extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    /**
     * @var SchoolLocation
     */
    protected $schoolLocation;

    /**
     * Create a new job instance.
     *
     * @param SchoolLocation $schoolLocation
     * @return void
     */
    public function __construct(SchoolLocation $schoolLocation)
    {
        //
        $this->schoolLocation = $schoolLocation;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $date = new Carbon();

        $count = $this->schoolLocation->licenses()->whereNotNull('end')->where('end', '<', $date->format('Y-m-d'))->sum('amount');

        $this->schoolLocation->setAttribute('count_expired_licenses', $count);
        $this->schoolLocation->save();
    }
}

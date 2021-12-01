<?php

namespace tcCore\Jobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\Role;
use tcCore\SchoolLocation;
use tcCore\SchoolLocationReport;
use tcCore\UserRole;

class UpdateSchoolLocationReportRecord extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var SchoolLocationId
     */
    protected $schoolLocationId;

    /**
     * Create a new job instance.
     *
     * @param $schoolLocationId
     * @return void
     */
    public function __construct($schoolLocationId)
    {
        //
        $this->schoolLocationId = $schoolLocationId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        try {
            SchoolLocationReport::updateLocationStats(SchoolLocation::withTrashed()->find($this->schoolLocationId));
        } catch (\Throwable $e){}
    }
}

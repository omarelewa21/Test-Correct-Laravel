<?php

namespace tcCore\Jobs;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\SchoolLocation;
use tcCore\SchoolLocationReport;

class CountSchoolLocationActiveLicenses extends Job implements ShouldQueue
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
//        $date = new Carbon();
//
//        $count = $this->schoolLocation->licenses()->where('start', '<=', $date->format('Y-m-d'))->where(function ($query) use ($date) {
//            $query->whereNull('end')->orWhere('end', '>=', $date->format('Y-m-d'));
//        })->sum('amount');
//
//        Log::debug('Schoollocation #' . $this->schoolLocation->getKey() . ' -> count_active_licenses: ' . $count);

        $this->schoolLocation->setAttribute('count_active_licenses', SchoolLocationReport::getActiveLicenses($this->schoolLocation));
        $this->schoolLocation->save();
    }
}

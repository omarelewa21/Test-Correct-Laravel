<?php

namespace tcCore\Jobs;

use Illuminate\Support\Facades\Log;
use tcCore\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\School;

class CountSchoolExpiredLicenses extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    /**
     * @var School
     */
    protected $school;

    /**
     * Create a new job instance.
     *
     * @param School $school
     * @return void
     */
    public function __construct(School $school)
    {
        //
        $this->school = $school;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $count = $this->school->schoolLocations()->sum('count_expired_licenses');

        Log::debug('School #'.$this->school->getKey().' -> count_expired_licenses: '.$count);

        $this->school->setAttribute('count_expired_licenses', $count);
        $this->school->save();
    }
}

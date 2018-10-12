<?php

namespace tcCore\Jobs;

use Illuminate\Support\Facades\Log;
use tcCore\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\UmbrellaOrganization;

class CountUmbrellaOrganizationLicenses extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    /**
     * @var UmbrellaOrganization
     */
    protected $umbrellaOrganization;

    /**
     * Create a new job instance.
     *
     * @param UmbrellaOrganization $umbrellaOrganization
     * @return void
     */
    public function __construct(UmbrellaOrganization $umbrellaOrganization)
    {
        //
        $this->umbrellaOrganization = $umbrellaOrganization;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $count = $this->umbrellaOrganization->schools()->sum('count_licenses');

        Log::debug('Umbrella Organization #'.$this->umbrellaOrganization->getKey().' -> count_licenses: '.$count);

        $this->umbrellaOrganization->setAttribute('count_licenses', $count);
        $this->umbrellaOrganization->save();
    }
}

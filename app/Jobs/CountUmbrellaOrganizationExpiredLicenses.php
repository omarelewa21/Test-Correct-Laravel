<?php

namespace tcCore\Jobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\UmbrellaOrganization;

class CountUmbrellaOrganizationExpiredLicenses extends Job implements ShouldQueue
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
        $count = $this->umbrellaOrganization->schools()->sum('count_expired_licenses');

        $this->umbrellaOrganization->setAttribute('count_expired_licenses', $count);
        $this->umbrellaOrganization->save();
    }
}

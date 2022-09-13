<?php

namespace tcCore\Jobs;

use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use tcCore\UmbrellaOrganization;

class CountUmbrellaOrganizationTests extends Job implements ShouldQueue
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
        $count = $this->umbrellaOrganization->schools()->sum('count_tests');

        $this->umbrellaOrganization->setAttribute('count_tests', $count);
        $this->umbrellaOrganization->save();
    }
}

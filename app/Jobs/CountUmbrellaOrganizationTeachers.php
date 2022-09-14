<?php

namespace tcCore\Jobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\UmbrellaOrganization;

class CountUmbrellaOrganizationTeachers extends Job implements ShouldQueue
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
        $count = $this->umbrellaOrganization->schools()->sum('count_teachers');

        $this->umbrellaOrganization->setAttribute('count_teachers', $count);
        $this->umbrellaOrganization->save();
    }
}

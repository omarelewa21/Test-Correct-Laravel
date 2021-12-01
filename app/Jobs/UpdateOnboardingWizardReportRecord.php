<?php

namespace tcCore\Jobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\OnboardingWizardReport;
use tcCore\UmbrellaOrganization;
use tcCore\User;

class UpdateOnboardingWizardReportRecord extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    /**
     * @var User
     */
    protected $user;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @return void
     */
    public function __construct(User $user)
    {
        //
        $this->user = $user;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        OnboardingWizardReport::updateForUser($this->user);
    }
}

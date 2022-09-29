<?php

namespace tcCore\Jobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\UmbrellaOrganization;
use tcCore\User;

class CountAccountManagerAccounts extends Job implements ShouldQueue
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
        $umbrellaOrganizationIds = UmbrellaOrganization::where('user_id', $this->user->getKey())->pluck('id')->all();
        $count = count($umbrellaOrganizationIds);


        $schoolIds = School::where('user_id', $this->user->getKey())->whereNotIn('umbrella_organization_id', $umbrellaOrganizationIds)->pluck('id')->all();
        $count += count($schoolIds);

        $count += SchoolLocation::where('user_id', $this->user->getKey())->whereNotIn('school_id', $schoolIds)->count();

        $this->user->setAttribute('count_accounts', $count);
        $this->user->save();
    }
}

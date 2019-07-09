<?php

namespace tcCore\Jobs;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\License;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\UmbrellaOrganization;
use tcCore\User;

class CountAccountManagerExpiredLicenses extends Job implements ShouldQueue
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
        $umbrellaOrganizations = UmbrellaOrganization::where('user_id', $this->user->getKey())->select(['id', 'count_expired_licenses'])->get();
        $count = 0;
        $umbrellaOrganizationIds = [];
        foreach ($umbrellaOrganizations as $umbrellaOrganization) {
            $count += $umbrellaOrganization->getAttribute('count_expired_licenses');
            $umbrellaOrganizationIds[] = $umbrellaOrganization->getKey();
        }

        $schools = School::where('user_id', $this->user->getKey())->whereNotIn('umbrella_organization_id', $umbrellaOrganizationIds)->get();
        $schoolIds = [];
        foreach ($schools as $school) {
            $count += $school->getAttribute('count_expired_licenses');
            $schoolIds[] = $school->getKey();
        }

        $schoolLocations = SchoolLocation::where('user_id', $this->user->getKey())->whereNotIn('school_id', $schoolIds)->get();
        foreach ($schoolLocations as $schoolLocation) {
            $count += $schoolLocation->getAttribute('count_expired_licenses');
        }

        Log::debug('Accountmanager #' . $this->user->getKey() . ' -> count_expired_licenses: ' . $count);

        $this->user->setAttribute('count_expired_licenses', $count);
        $this->user->save();
    }
}

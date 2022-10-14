<?php

namespace tcCore\Jobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\Role;
use tcCore\SchoolLocation;
use tcCore\UserRole;

class CountSchoolLocationTests extends Job implements ShouldQueue
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
        $count = $this->schoolLocation->users()->notDemo()->whereIn('id', function ($query) {
            $userRole = new UserRole();
            $query->select('user_id')->from($userRole->getTable())->whereIn('role_id', function ($query) {
                $role = new Role();
                $query->select('role_id')->from($role->getTable())->where('name', 'Teacher')->whereNull('deleted_at');
            })->whereNull('deleted_at');
        })->whereNull('school_id')->sum('count_tests');

        $this->schoolLocation->setAttribute('count_tests', $count);
        $this->schoolLocation->save();
    }
}

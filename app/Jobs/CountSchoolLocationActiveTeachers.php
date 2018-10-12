<?php

namespace tcCore\Jobs;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use tcCore\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\Role;
use tcCore\SchoolLocation;
use tcCore\UserRole;

class CountSchoolLocationActiveTeachers extends Job implements SelfHandling, ShouldQueue
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
        $date = new Carbon();
        $date->subMonths(3);

        $count = $this->schoolLocation->users()->whereIn('id', function($query) {
            $userRole = new UserRole();
            $query->select('user_id')->from($userRole->getTable())->whereIn('role_id', function($query) {
                $role = new Role();
                $query->select($role->getKeyName())->from($role->getTable())->where('name', 'Teacher')->whereNull('deleted_at');
            })->whereNull('deleted_at');
        })->whereNull('school_id')->where('count_last_test_taken', '>=', $date->format('Y-m-d'))->count();

        Log::debug('Schoollocation #'.$this->schoolLocation->getKey().' -> count_active_teachers: '.$count);

        $this->schoolLocation->setAttribute('count_active_teachers', $count);
        $this->schoolLocation->save();
    }
}

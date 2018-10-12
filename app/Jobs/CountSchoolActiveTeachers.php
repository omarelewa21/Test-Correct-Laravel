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
use tcCore\School;
use tcCore\UserRole;

class CountSchoolActiveTeachers extends Job implements SelfHandling, ShouldQueue
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
        $date = new Carbon();
        $date->subMonths(3);

        $count = $this->school->users()->whereIn('id', function($query) {
            $userRole = new UserRole();
            $query->select('user_id')->from($userRole->getTable())->whereIn('role_id', function($query) {
                $role = new Role();
                $query->select($role->getKeyName())->from($role->getTable())->where('name', 'Teacher')->whereNull('deleted_at');
            })->whereNull('deleted_at');
        })->where('count_last_test_taken', '>=', $date->format('Y-m-d H:i:s'))->count();

        $count += $this->school->schoolLocations()->sum('count_active_teachers');

        Log::debug('School #'.$this->school->getKey().' -> count_active_teachers: '.$count);

        $this->school->setAttribute('count_active_teachers', $count);
        $this->school->save();
    }
}

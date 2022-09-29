<?php

namespace tcCore\Jobs;

use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\Role;
use tcCore\School;
use tcCore\UserRole;

class CountSchoolStudents extends Job implements ShouldQueue
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
        $count = $this->school->users()->whereIn('id', function($query) {
            $userRole = new UserRole();
            $query->select('user_id')->from($userRole->getTable())->whereIn('role_id', function($query) {
                $role = new Role();
                $query->select($role->getKeyName())->from($role->getTable())->where('name', 'Student')->whereNull('deleted_at');
            })->whereNull('deleted_at');
        })->count();

        $count += $this->school->schoolLocations()->sum('count_students');

        $this->school->setAttribute('count_students', $count);


                $countText2Speech = $this->school->users()
                        ->whereIn('id', function($query) {
                        $userRole = new UserRole();
                        $query->select('user_id')->from($userRole->getTable())->whereIn('role_id', function($query) {
                                $role = new Role();
                                $query->select($role->getKeyName())->from($role->getTable())->where('name', 'Student')->whereNull('deleted_at');
                            })->whereNull('deleted_at');
                    })
                ->where('text2speech','=',1)
                ->count();

        $countText2Speech = $this->school->schoolLocations()->sum('count_text2speech');

        $this->school->setAttribute('count_text2speech', $countText2Speech);
        
        $this->school->save();
    }
}

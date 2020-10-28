<?php

namespace tcCore\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\User;

class SetSchoolYearForDemoClassToCurrent implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $schoolLocation;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(SchoolLocation $schoolLocation)
    {
        $this->schoolLocation = $schoolLocation;
        //
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        // find a teacher within this school location with a demo class
        $demoClass = SchoolClass::where('school_location_id',$this->schoolLocation->getKey())
            ->where('name',DemoHelper::CLASSNAME)->first();
        // act as this user to get the current school year
        if(!$demoClass) return false;

        $id = collect(DB::select('
        select users.id from users inner join teachers on (users.id=user_id) where school_location_id = ?
        and users.deleted_at is null and teachers.deleted_at is null
        ', [$this->schoolLocation->getKey()]))->pluck('id')->first();

        if(!$id) return false;

        ActingAsHelper::getInstance()->setUser(User::find($id));
        if($currentSchoolYear = SchoolYearRepository::getCurrentSchoolYear()){
            if($currentSchoolYear != $demoClass->schoolYear){
                $demoClass->demoRestrictionOverrule = true;
                $demoClass->school_year_id = $currentSchoolYear->getKey();
                $demoClass->save();
            }
        }

        return true;
    }
}

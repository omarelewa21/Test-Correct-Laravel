<?php

namespace tcCore\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use tcCore\Jobs\CountSchoolActiveTeachers;
use tcCore\Jobs\CountSchoolLocationQuestions;
use tcCore\User;

class StartActiveTeacherJobs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cron:active-teacher';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Schedules jobs to update active teacher counters.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $date = new Carbon();
        $date->subDays(1);

        $inactiveTeachers = User::where('count_last_test_taken', $date)->with('school', 'schoolLocation')->get();
        foreach ($inactiveTeachers as $user) {
            $school = $user->school;
            if ($school !== null) {
                $user->dispatch(new CountSchoolActiveTeachers($school));
            } else {
                $schoolLocation = $user->schoolLocations;
                if ($schoolLocation !== null) {
                    $user->dispatch(new CountSchoolLocationQuestions($schoolLocation));
                }
            }
        }
    }
}

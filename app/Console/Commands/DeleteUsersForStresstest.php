<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use tcCore\Log;
use tcCore\SchoolClass;
use tcCore\SchoolLocation;
use tcCore\SchoolLocationSchoolYear;
use tcCore\Student;
use tcCore\Teacher;
use tcCore\User;
use tcCore\UserRole;

class DeleteUsersForStresstest extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stresstest:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete related data for stresstest';

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
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $schoolLocationName = 'stresstest';

        $schoolLocations = SchoolLocation::where('name','like','stresstest%')->get();

        if(!$schoolLocations || $schoolLocations->count() < 1 ){
            $this->error('Nothing to delete');
            return true;
        }

        if ($this->confirm(sprintf('Are you sure you want to delete all data for the %d stresstest schoold?',$schoolLocations->count()))) {
            $this->info('we\'re going to delete all data, this can take some time...');
            $bar = $this->output->createProgressBar($schoolLocations->count());

            $this->deleteData($schoolLocations, $bar);
            $this->info('');
            $this->line('Deleted all data');
        }
        else{
            $this->error('Didn\'t delete anything!');
        }
        return true;
    }

    protected function deleteData($schoolLocations, $bar)
    {
        $schoolLocations->each(function($location) use ($bar){
           $this->deleteLocation($location);
           $bar->advance();
        });
    }

    protected function deleteLocation($location)
    {
        $location->schoolLocationSchoolYears()->forceDelete();
        $location->schoolClasses()->forceDelete();
        $location->users()->forceDelete();
        $location->forceDelete();
        return true;
    }

}

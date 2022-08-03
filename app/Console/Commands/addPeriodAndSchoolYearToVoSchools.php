<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use tcCore\SchoolLocation;

class addPeriodAndSchoolYearToVoSchools extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'school_locations:add_new_period';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'adds period and schoolyear 2021 to all school locations of type vo that are activated.';

    private $locationWithoutUser =[];

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
     * @return int
     */
    public function handle()
    {
        set_time_limit(0);
        ini_set('memory_limit', '-1');

        $year = Date("Y");
        $nextYear = $year+1;
        $locations = SchoolLocation::NoActivePeriodAtDate($year.'-08-01')->activeOnly()->get();
        $bar = $this->output->createProgressBar($locations->count());
         $locations->each(function ($location) use($year, $nextYear, $bar){
            $user = $location->users()->first();
            if ($user == null) {
                $this->locationWithoutUser[] = $location->getKey();
            } else {
                Auth::login($user);
                $location->addSchoolYearAndPeriod($year, '01-08-'.$year, '31-07-'.$nextYear);
//                $this->info(
//                    sprintf('added for school location %s (%d)', $location->name, $location->getKey())
//                );
            }
             $bar->advance();
        });
         $bar->finish();
         $this->info(sprintf('%s done',PHP_EOL));
        if ($this->locationWithoutUser) {
            $this->error(
                sprintf("%s no new school year was created for location(s) with id: [%s]", PHP_EOL,implode(',',
                    $this->locationWithoutUser))
            );
        }

        return 0;
    }
}

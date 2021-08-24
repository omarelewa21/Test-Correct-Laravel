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
        SchoolLocation::noActivePeriodAtDate('01-08-2021')->get()->each(function ($location) {
            $user = $location->users()->first();
            if ($user == null) {
                $this->locationWithoutUser[] = $location->getKey();
            } else {
                Auth::login($user);
                $location->addSchoolYearAndPeriod('2021', '01-08-2021', '31-07-2022');
            }
        });
        if ($this->locationWithoutUser) {
            $this->error(
                sprintf("no new school year was created for location(s) with id: [%s]", implode($this->locationWithoutUser, ','))
            );
        }

        return 0;
    }
}

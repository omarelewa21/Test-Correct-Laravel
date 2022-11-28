<?php namespace tcCore\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Jobs\AnonymizeUsersAfterTooLongNoLoginJob;

class Kernel extends ConsoleKernel
{


    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
//		$schedule->command('cron:license')
//				 ->daily();
//		$schedule->command('cron:teacher')
//			->daily();
        $schedule->command('school_location_report:update')
            ->dailyAt('02:00');
        $schedule->command('requestlog:clear 5 --silent')
            ->dailyAt('04:00');
        $schedule->command('telescope:prune')->daily();
        $schedule->command('onboarding_wizard_report:update')
            ->dailyAt('06:00');


        /**
         * once we are at laravel 8, we can change this to the following schedule
         *         $schedule->command('school_locations:add_new_period')
         *          ->yearlyOn(8, 1, '06:00'); // only available in laravel 8 and up
         */
        $schedule->command('school_locations:add_new_period')
            ->monthlyOn(1,'06:00')
            ->when(function(){
               return ((int) date('m') === 8); // only on the first of august
            });

//        $schedule->command('school_locations:add_new_period')
//            ->yearlyOn(8, 1, '06:00'); // only available in laravel 8 and up

//        $schedule->call(new AnonymizeUsersAfterTooLongNoLoginJob())
//            ->dailyAt('05:00');
// one minute past the hour;
//        if(BaseHelper::notProduction()){
//            $schedule->command('assessment:start_and_stop')->everyMinute();
//        } else {
//            $schedule->command('assessment:start_and_stop')->hourlyAt(1);
//        }
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }

}

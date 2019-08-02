<?php namespace tcCore\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use tcCore\Console\Commands\ClearOldRequestLogs;
use tcCore\Console\Commands\DeleteUsersForStresstest;
use tcCore\Console\Commands\GenerateUsersForStresstest;
use tcCore\Console\Commands\ProductionPullFromGit;
use tcCore\Console\Commands\StresstestSetup;
use tcCore\Console\Commands\RefreshDatabase;
use tcCore\Console\Commands\RestoreUser;
use tcCore\Console\Commands\StresstestTeardown;

class Kernel extends ConsoleKernel
{

    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        'tcCore\Console\Commands\Inspire',
        'tcCore\Console\Commands\GenerateUser',
        'tcCore\Console\Commands\StartLicenseJobs',
        'tcCore\Console\Commands\StartActiveTeacherJobs',
        RefreshDatabase::class,
        ClearOldRequestLogs::class,
        RestoreUser::class,
        GenerateUsersForStresstest::class,
        DeleteUsersForStresstest::class,
        StresstestSetup::class,
        StresstestTeardown::class,
        ProductionPullFromGit::class
    ];

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
        $schedule->command('requestlog:clear 5 --silent')
            ->dailyAt('04:00');
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }

}

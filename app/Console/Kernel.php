<?php namespace tcCore\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use tcCore\Console\Commands\ClearOldRequestLogs;

use tcCore\Console\Commands\CountOnlineUsers;
use tcCore\Console\Commands\DeleteUsersForStresstest;
use tcCore\Console\Commands\GenerateUsersForStresstest;

use tcCore\Console\Commands\RefreshDatabase;
use tcCore\Console\Commands\RestoreUser;

class Kernel extends ConsoleKernel {

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
        CountOnlineUsers::class,
        GenerateUsersForStresstest::class,
        DeleteUsersForStresstest::class
	];

	/**
	 * Define the application's command schedule.
	 *
	 * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
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

}

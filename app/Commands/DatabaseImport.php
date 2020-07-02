<?php namespace tcCore\Commands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class DatabaseImport
{
	private static function checkEnv()
	{
		if (!in_array(env('APP_ENV'), ['local', 'testing'])) {
            exit();
		} else {
			return true;
		}
	}

	public static function importSql($file)
	{
        DatabaseImport::checkEnv();
		ini_set('max_execution_time', 180);

		$host = DB::connection()->getConfig('host');
		$portString = '';
		if (strlen(env('DB_PORT')) > 1) {
			$portString = sprintf(' --port %d ', env('DB_PORT'));
			$host = explode(':', $host)[0];
		}

        //FIXME: TC-138
		//IT IS UNSAFE TO PARSE PASSWORDS OVER COMMANDLINE!!!
		$command = sprintf(
			'mysql -h %s %s -u %s -p%s %s < %s',
			$host,
			$portString,
			DB::connection()->getConfig('username'),
			DB::connection()->getConfig('password'),
			DB::connection()->getConfig('database'),
			$file
        );

        $process = new Process($command);
        $process->run();
	}

	public static function migrate()
	{
		DatabaseImport::checkEnv();

		Artisan::call('migrate', ['--force' => true]);
	}
}

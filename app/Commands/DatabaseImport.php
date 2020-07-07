<?php namespace tcCore\Commands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\SchoolLocation;

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

	public static function addRequiredDatabaseData() {
		// fix issue with missing temp school location if sovag
		if(null == SchoolLocation::where('customer_code','TC-tijdelijke-docentaccounts')->first()){
			SchoolLocation::where('id',1)->update(['customer_code' =>'TC-tijdelijke-docentaccounts']);
		}

		if (SchoolLocation::where('customer_code', DemoHelper::SCHOOLLOCATIONNAME)->first() == null) {
			$demoSchool = SchoolLocation::find(1)->replicate();
			$demoSchool->customer_code = DemoHelper::SCHOOLLOCATIONNAME;
			$demoSchool->save();
		}
	}
}

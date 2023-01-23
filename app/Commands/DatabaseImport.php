<?php namespace tcCore\Commands;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\SchoolLocation;
use tcCore\Teacher;

class DatabaseImport
{
    private static function checkEnv()
    {
        if (!in_array(config('app.env'), ['local', 'testing'])) {
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
        if (strlen(config('connections.mysql.port')) > 1) {
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

        if (DB::connection()->getConfig('password') == '') {
            $command = sprintf(
                'mysql -h %s %s -u %s %s < %s',
                $host,
                $portString,
                DB::connection()->getConfig('username'),
                DB::connection()->getConfig('database'),
                $file
            );
        }

        $process = Process::fromShellCommandline($command);
        $process->run();
    }

    public static function migrate()
    {
        DatabaseImport::checkEnv();

//        $process = Process::fromShellCommandline('artisan migrate --force');
//        $process->run();

        Artisan::call('migrate --force');
    }

    public static function addRequiredDatabaseData()
    {


        DatabaseImport::checkEnv();

        // fix issue with missing temp school location if sovag
        if (null == SchoolLocation::where('customer_code', 'TC-tijdelijke-docentaccounts')->first()) {
            SchoolLocation::where('id', 1)->update(['customer_code' => 'TC-tijdelijke-docentaccounts']);
        }

        // not needed => tc-tijdelijke-docentaccounts should be enough
//		if (SchoolLocation::where('customer_code', DemoHelper::SCHOOLLOCATIONNAME)->first() == null) {
//			$demoSchool = SchoolLocation::find(1)->replicate();
//			$demoSchool->customer_code = DemoHelper::SCHOOLLOCATIONNAME;
//			$demoSchool->save();
//		}

        //TCP-156
        $teacherUsers = Teacher::with('user')->get()->map(function ($t) {
            return $t->user;
        })->unique('id');

        // if run in selenium test we need to relogin the current user
        $origUser = Auth::user();

        foreach ($teacherUsers as $teacher) {
            Auth::loginUsingId($teacher->getKey());
            ActingAsHelper::getInstance()->setUser($teacher);
            (new DemoHelper)->createDemoForTeacherIfNeeded($teacher);
        }

        if (null !== $origUser) {
            Auth::loginUsingId($origUser->getKey());
        }

        $schoolLocations = SchoolLocation::all();
        foreach ($schoolLocations as $schoolLocation){
            if(!is_null($schoolLocation->allow_new_player_access)){
                continue;
            }
            $schoolLocation->allow_new_player_access = true;
            $schoolLocation->save();
        }
    }
}

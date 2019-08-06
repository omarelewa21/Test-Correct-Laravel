<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;


use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
class StresstestSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stresstest:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prepare for stresstest';

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
        $envFile = '.env';
        $envBackupFileWhileStresstest = ".envBackupWhileStresstest";
        if(file_exists($envBackupFileWhileStresstest)) {
            print_r([
                config('app.env'),
            ]);
            exit;
        }
        if (config('app.env') !== 'local') {

            if(!file_exists($envBackupFileWhileStresstest)) {
                $this->error('You cannot perform this action on this environment! only with APP_ENV set to local AND not in production (read config:cache && route:cache)!!');
                return false;
            }
            else{
                if($this->confirm('You didn\'t do a teardown first, do you want to prepare for another stresstest?')){
                    $this->info('we\'re going to do a teardown first');
                    Artisan::call(sprintf('stresstest:teardown'));
                    $this->info('teardown done');
                }
                else{
                    $this->error('Stresstest setup was cancelled by you');
                    return false;
                }
            }
        }

        // this might be slow, so give us some time
        ini_set('max_execution_time', 180); //3 minutes

        if(!file_exists($envFile)){
            $this->error('could not find the '.$envFile.' file');
            return false;
        }

        $sqlImports = [
            'stresstestdb.sql',
        ];

        $this->info('start refreshing database...(this can take some time as in several minutes)');
        if(!$this->handleSqlFiles($sqlImports)){
            return false;
        }

        $this->addMigrations();
        $this->info('refresh database complete');

        $this->info(PHP_EOL);

        if(!file_exists($envBackupFileWhileStresstest)) {
            $this->info('going to set env settings to production');
            $this->printSubItem('make backup of ' . $envFile . ' to ' . $envBackupFileWhileStresstest);
            $envContents = file_get_contents($envFile);
            $this->info('done');
            $this->printSubItem('set app_env  to production and debug to false');

            file_put_contents($envBackupFileWhileStresstest, $envContents);

            $envContents = str_replace('APP_ENV=local', 'APP_ENV=production', $envContents);
            $envContents = str_replace('APP_DEBUG=true', 'APP_DEBUG=false', $envContents);
            $envContents = str_replace('QUEUE_DRIVER=sync', 'QUEUE_DRIVER=database', $envContents);

            file_put_contents($envFile, $envContents);

            $this->info('done');
        }

        /**
         * added to separate file and needs to be called independently php artisan stresstest:setup && php artisan stresstest:cache
         */
//        $this->info('Start caching');

//        foreach(['config','route','view'] as $type){
//            $this->printSubItem($type.' cache');
//            Artisan::call(sprintf('%s:cache',$type));
//            $this->info('done');
//        };
//        $this->info('caching complete');

        $this->info(PHP_EOL);
        $this->info(PHP_EOL);

        $this->alert('You\'re ready to do the stresstest, DON\'T forget to do a stresstest:teardown afterwards, or before the next stresstest run');
    }

    protected function printSubItem($message){
        $this->output->write('<info>  o '.$message.'...</info>',false);
    }

    protected function addMigrations(){
        $this->printSubItem('going to put the migrations on top');
        Artisan::call('migrate');
        $this->info('done');
    }

    protected function handleSqlFiles($sqlImports = []){
        $path = base_path('database/seeds/');
        foreach ($sqlImports as $file) {
            $file = sprintf('%s%s',$path,$file);
            if(!file_exists($file)){
                $this->error('The file '.$file.' doesn\'t seem to exist, we can\'t do a proper setup');
                return false;
            }
        }
        foreach ($sqlImports as $file) {
            $this->printSubItem(sprintf('importing %s...',$file));
            $file = sprintf('%s%s',$path,$file);
            $command = sprintf(
                'mysql -h %s -u %s -p%s %s < %s',
                DB::connection()->getConfig('host'),
                DB::connection()->getConfig('username'),
                DB::connection()->getConfig('password'),
                DB::connection()->getConfig('database'),
                $file
            );

//            $this->info('command runned: '.$command);

            $process = new Process($command);
            $process->run();
            $this->info('done');
        }
        return true;
    }

}

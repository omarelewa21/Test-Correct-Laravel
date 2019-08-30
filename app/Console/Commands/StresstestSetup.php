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
    protected $signature = 'stresstest:setup {--skipDB : skip database, --forceTeardown : do we need to force a}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prepare for stresstest';

    protected $envFile = '.env';
    protected $envBackupFileWhileStresstest = ".envBackupWhileStresstest";
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

        if (config('app.env') !== 'local') {
            if(!$this->hasStresstestSetup()) {
                $this->error('You cannot perform this action on this environment! only with APP_ENV set to local AND not in production (read config:cache && route:cache)!!');
                return false;
            }
            else if($this->option('forceTeardown')){
                $this->info('we\'re going to do a teardown first');
                Artisan::call(sprintf('stresstest:teardown'));
                $this->info('teardown done');
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

        if(!file_exists($this->envFile)){
            $this->error('could not find the '.$this->envFile.' file');
            return false;
        }

        if(!$this->option('skipDB')){
            $sqlImports = [
                'stresstestdb.sql',
            ];

            $this->info('start refreshing database...(this can take some time as in several minutes)');
            if (!$this->handleSqlFiles($sqlImports)) {
                return false;
            }

            $this->addMigrations();
            $this->info('refresh database complete');

            $this->info(PHP_EOL);
        }

        $this->composerInstall();

        if(!$this->hasStresstestSetup()) {
            $this->info('going to set env settings to production');
            $this->printSubItem('make backup of ' . $this->envFile . ' to ' . $this->envBackupFileWhileStresstest);
            $envContents = file_get_contents($this->envFile);
            $this->info('done');
            $this->printSubItem('set app_env  to production and debug to false');

            file_put_contents($this->envBackupFileWhileStresstest, $envContents);

            $envContents = str_replace('APP_ENV=local', 'APP_ENV=production', $envContents);
            $envContents = str_replace('APP_DEBUG=true', 'APP_DEBUG=false', $envContents);
            $envContents = str_replace('QUEUE_DRIVER=sync', 'QUEUE_DRIVER=database', $envContents);

            file_put_contents($this->envFile, $envContents);

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

        $this->alert('You\'re ready to do the stresstest, DON\'T forget to run stresstest:cache as well if you didn\'t do so yet!!');
        return true;
    }

    protected function printSubItem($message){
        $this->output->write('<info>  o '.$message.'...</info>',false);
    }

    protected function addMigrations(){
        $this->printSubItem('going to put the migrations on top');
        $this->call('migrate',['--force' => true,]);
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

    protected function hasStresstestSetup(){
        if(!file_exists($this->envBackupFileWhileStresstest)){
            die('error searching for the '.$this->envBackupFileWhileStresstest.' file');
        }
        if(file_get_contents($this->envBackupFileWhileStresstest) == 1){
            return false;
        }
        else{
            return true;
        }
    }


    // composer install with --NO-DEV option
    protected function composerInstall(){
        $this->info('going to do a composer install with no dev option');
        if(!exec('php composer.phar install --optimize-autoloader --no-dev')){
            $this->error('an error occured while doing a composer install, please take care manually');
        }
        $this->info('done');
        $this->info(PHP_EOL);
    }
}

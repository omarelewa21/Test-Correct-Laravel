<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class StresstestTeardown extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stresstest:teardown';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Teardown for stresstest {--skipDB : skip testdb reload';

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
        // this might be slow, so give us some time
        ini_set('max_execution_time', 180); //3 minutes


        $this->info('Remove caching');
        foreach(['route','config','view'] as $type){
            $this->printSubItem($type.' cache');
            Artisan::call(sprintf('%s:clear',$type));
            $this->info('done');
        };
        $this->info('Remove caching complete');

        $this->info(PHP_EOL);

        if(!$this->hasStresstestSetup()){
            $this->error('it seems like you didn\'t do a proper setup, please reset the '.$this->envFile.' file yourself');
            $this->error('APP_ENV=local && APP_DEBUG=true');
            return false;
        }

        $this->info('going to set env settings back to what it was');
        $envContents = file_get_contents($this->envBackupFileWhileStresstest);
        file_put_contents($this->envFile,$envContents);
        file_put_contents($this->envBackupFileWhileStresstest,'1');
        $this->info(PHP_EOL);

        if(!$this->option('skipDB')) {
            $this->info('going to put the default testdb back');
            exec('php artisan test:refreshdb');
            $this->info('done');
            $this->info(PHP_EOL);
        }

        $this->composerInstall();

        $this->info('You\'re all done!');
    }

    protected function hasStresstestSetup(){
        if(!file_exists($this->envBackupFileWhileStresstest)){
            die('error searching for the '.$this->envBackupFileWhileStresstest.' file');
        }
        if(file_get_contents($this->envBackupFileWhileStresstest == 1)){
            return false;
        }
        else{
            return true;
        }
    }

    protected function printSubItem($message){
        $this->output->write('<info>  o '.$message.'...</info>',false);
    }

    // composer install with all options including dev
    protected function composerInstall(){
        $this->info('going to do a composer install with all options including dev');
        if(!exec('php composer.phar install')){
            $this->error('an error occured while doing a composer install, please take care manually');
        }
        $this->info('done');
        $this->info(PHP_EOL);
    }

}

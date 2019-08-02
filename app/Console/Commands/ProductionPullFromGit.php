<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class ProductionPullFromGit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'production:pull';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Pull from git and refresh config, routes, views and reinit queue';

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

        if (config('app.env') !== 'production') {
            $this->error('You cannot perform this action on this environment! only with APP_ENV set to production');
            return false;
        }

        $this->info('Going to pull the latest info from git');
            if(!exec('git pull')){
                $this->error('I\'m sorry, but we couldn\'t pull the latest data from git, please fix this first');
                return false;
            };
        $this->info('done');

        $this->info(PHP_EOL);

        if($this->confirm('Do you wan\'t to do a forced migration if needed?')){
            $this->addMigrations();
        }

//        if($this->confirm('Do you wan\'t to do a composer install if needed?')){
//            $this->composerInstall();
//        }


        $this->info('Start caching');
        foreach(['route','config','view'] as $type){
            $this->printSubItem($type.' cache');
            Artisan::call(sprintf('%s:clear',$type));
            Artisan::call(sprintf('%s:cache',$type));
            $this->info('done');
        };
        $this->info('caching complete');

        $this->info(PHP_EOL);

        $this->info('Restarting queue');
        Artisan::call('queue:restart');
        $this->info('done');

    }

    protected function composerInstall(){
        $this->info('going to do a composer install');
        if(!exec('composer install --optimize-autoloader --no-dev')){
            $this->error('an error occured while doing a composer install, please take care manually');
        }
        $this->info('done');
        $this->info(PHP_EOL);
    }



    protected function addMigrations(){
        $this->info('going to put the migrations on top');
        Artisan::call('migrate --force');
        $this->info('done');
        $this->info(PHP_EOL);
    }

    protected function printSubItem($message){
        $this->output->write('<info>  o '.$message.'...</info>',false);
    }
}

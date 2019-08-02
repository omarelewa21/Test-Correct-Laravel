<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

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

        if (env('APP_ENV') !== 'local') {
            $this->error('You cannot perform this action on this environment! only with APP_ENV set to local AND not in production (read config:cache && route:cache)!!');
            return false;
        }

        // this might be slow, so give us some time
        ini_set('max_execution_time', 180); //3 minutes

        if(!file_exists($envFile)){
            $this->error('could not find the '.$envFile.' file');
            return false;
        }

        $sqlImports = [
            'streetestdb.sql',
        ];

        $this->info('start refreshing database...(this can take some time as in several minutes)');
        if(!$this->handleSqlFiles($sqlImports)){
            return false;
        }

        $this->addMigrations();
        $this->info('refresh database complete');

        $this->info(PHP_EOL);

        $this->info('Start caching');
        foreach(['route','config','view'] as $type){
            $this->printSubItem($type.' cache');
            Artisan::call(sprintf('%s:clear',$type));
            Artisan::call(sprintf('%s:cache',$type));
            $this->info('done');
        };
        $this->info('caching complete');

        $this->info(PHP_EOL);

        $this->info('going to set env settings to production');
        $this->printSubItem('make backup of '.$envFile.' to '.$envBackupFileWhileStresstest);
        $envContents = file_get_contents($envFile);
        $this->info('done');
        $this->printSubItem('set app_env  to production and debug to false');
        file_put_contents($envBackupFileWhileStresstest,$envContents);
        $envContents = str_replace('APP_ENV=local','APP_ENV=production',$envContents);
        $envContents = str_replace('APP_DEBUG=true','APP_DEBUG=false',$envContents);
        file_put_contents($envFile,$envContents);
        $this->info('done'.PHP_EOL.PHP_EOL);
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
        foreach ($sqlImports as $file) {
            if(!file_exists($file)){
                $this->error('The file '.$file.' doesn\'t seem to exist, we can\'t do a proper setup');
                return false;
            }
        }
        foreach ($sqlImports as $file) {
            $this->printSubItem(sprintf('importing %s...',$file));
            $command = sprintf(
                'mysql -h %s -u %s -p%s %s < database/seeds/%s',
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

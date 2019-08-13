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
    protected $description = 'Teardown for stresstest';

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

        $envFile = '.env';
        $envBackupFileWhileStresstest = ".envBackupWhileStresstest";

        $this->info('Remove caching');
        foreach(['route','config','view'] as $type){
            $this->printSubItem($type.' cache');
            Artisan::call(sprintf('%s:clear',$type));
            $this->info('done');
        };
        $this->info('Remove caching complete');

        $this->info(PHP_EOL);

        if(!file_exists($envBackupFileWhileStresstest)){
            $this->error('could not find the '.$envBackupFileWhileStresstest.' file');
            $this->error('it seems like you didn\'t do a proper setup, please reset the '.$envFile.' file yourself');
            $this->error('APP_ENV=local && APP_DEBUG=true');
            return false;
        }

        $this->info('going to set env settings back to what it was');
        $envContents = file_get_contents($envBackupFileWhileStresstest);
        file_put_contents($envFile,$envContents);
        unlink($envBackupFileWhileStresstest);
        $this->info(PHP_EOL);
        $this->info('You\'re all done!');
    }

    protected function printSubItem($message){
        $this->output->write('<info>  o '.$message.'...</info>',false);
    }

}

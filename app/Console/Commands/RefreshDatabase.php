<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class RefreshDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:refreshdb';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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

        $sqlImports = [
            'tccore_dev_safe.sql',
        ];

        $this->info('start refreshing database...(this can take some time as in several minutes)');
        // only needed when using mysql database, not when sqlite setup is needed
        switch(DB::connection()->getConfig('name')){
            case 'sqlite':
                // do nothing
                break;
            case 'mysql':
            default:
                $this->rollbackMigrations();
                $this->handleSqlFiles($sqlImports);
                break;
        }

        $this->addMigrations();
        $this->info('refresh database complete');
    }

    protected function addMigrations(){
        $this->output->write('<info>  o going to put the migrations on top...</info>',false);
        Artisan::call('migrate');
        $this->info('done');
    }

    protected function rollbackMigrations(){
        $this->output->write('<info>  o start rollback migrations...</info>',false);
        Artisan::call('migrate:rollback');
        $this->info('done');
    }

    protected function handleSqlFiles($sqlImports = []){
        foreach ($sqlImports as $file) {
            $this->output->write(sprintf('<info>  o importing %s...</info>',$file),false);
            $command = sprintf(
                'mysql -u %s -p%s %s < database/seeds/%s',
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
    }
}

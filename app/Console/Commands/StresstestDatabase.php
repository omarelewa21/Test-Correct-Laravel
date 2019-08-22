<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;


use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
class StresstestDatabase extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stresstest:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Load stresstest database';

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
            'stresstestdb.sql',
        ];

        $this->info('start refreshing database...(this can take some time as in several minutes)');
        if(!$this->handleSqlFiles($sqlImports)){
            return false;
        }

        $this->addMigrations();
        $this->info('refresh database complete');

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

            $process = new Process($command);
            $process->run();
            $this->info('done');
        }
        return true;
    }
}

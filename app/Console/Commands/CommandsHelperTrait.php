<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 15/10/2019
 * Time: 13:50
 */

namespace tcCore\Console\Commands;


use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

Trait CommandsHelperTrait
{
    protected function printSubItem($message){
        $this->output->write('<info>  o '.$message.'...</info>',false);
    }

    protected function addMigrations(){
        $this->printSubItem('going to put the migrations on top');
        $this->call('migrate',['--force' => true,]);
        $this->info('done');
    }

    protected function rollbackMigrations()
    {
        $this->printSubItem('start rollback migrations');
        Artisan::call('migrate:rollback');
        $this->info('done');
    }

    protected function handleSqlFiles($sqlImports = [])
    {
        foreach ($sqlImports as $file) {
            $this->printSubItem(sprintf('importing %s...',$file));

            $host = DB::connection()->getConfig('host');
            $portString = '';
            if(strlen(env('DB_PORT')) > 1){
                $portString = sprintf(' --port %d ',env('DB_PORT'));
                $host = explode(':',$host)[0];
            }
            $command = sprintf(
                'mysql -h %s %s -u %s -p%s %s < database/seeds/%s',
                $host,
                $portString,
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
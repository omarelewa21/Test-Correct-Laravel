<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 15/10/2019
 * Time: 13:50
 */

namespace tcCore\Console\Commands;


use Illuminate\Support\Facades\Artisan;
use tcCore\Commands\DatabaseImport;

trait CommandsHelperTrait
{
    protected function printSubItem($message)
    {
        $this->output->write('<info>  o ' . $message . '...</info>', false);
    }

    protected function addMigrations()
    {
        $this->printSubItem('going to put the migrations on top');
        DatabaseImport::migrate();
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
            $this->printSubItem(sprintf('importing %s...', $file));

            DatabaseImport::importSql($file);

            $this->info('done');
        }
        return true;
    }

    protected function addRequiredDatabaseData()
    {
        $this->printSubItem('add required data to database');
        DatabaseImport::addRequiredDatabaseData();
        $this->info('done');
    }

    protected function runseeder()
    {
        Artisan::call('db:seed');
        $command = 'setup_tests:scaffold';
        if($this->getApplication()->has($command)) {
            Artisan::call($command, ['user_id' => '1486']);
        }
    }
}

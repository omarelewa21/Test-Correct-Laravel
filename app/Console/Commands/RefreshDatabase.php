<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class RefreshDatabase extends Command
{
    use CommandsHelperTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:refreshdb {--file=}';

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
        $sqlImports = [
            'dropAllTablesAndViews.sql',
            'testdb.sql',
            'attainments.sql',
        ];

        file_put_contents(public_path() . "/waiting.txt", "1");
        
        if ($this->hasOption('file') && $this->option('file') != null) {
            $sqlImports = [
                sprintf('testing/db_dump_%s.sql', $this->option('file')),
            ];
        }


        if (!in_array(env('APP_ENV'), ['local', 'testing'])) {
            $this->error('You cannot perform this action on this environment! only with APP_ENV set to local!!');
            return false;
        }

        // this might be slow, so give us some time
        ini_set('max_execution_time', 180); //3 minutes

        $this->info('start refreshing database...(this can take some time as in several minutes)');
        // only needed when using mysql database, not when sqlite setup is needed
        switch (DB::connection()->getConfig('name')) {
            case 'sqlite':
                // do nothing
                break;
            case 'mysql':
            default:
                $this->handleSqlFiles($sqlImports);
                break;
        }

        $this->addMigrations();

        file_put_contents(public_path() . "/waiting.txt", "0");

        $this->info('refresh database complete');
    }
}

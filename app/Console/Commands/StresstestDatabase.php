<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;


use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
class StresstestDatabase extends Command
{
    use CommandsHelperTrait;

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


}

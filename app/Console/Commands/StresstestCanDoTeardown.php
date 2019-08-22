<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;


use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
class StresstestCanDoTeardown extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stresstest:cando-teardown';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Can there be a stresstest teardown';

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
        $envBackupFileWhileStresstest = ".envBackupWhileStresstest";
        echo (string) config('app.env') === 'production' && file_exists($envBackupFileWhileStresstest);
    }

}

<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;


use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
class StresstestCanDoSetup extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stresstest:cando-setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Can there be a stresstest';

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
        echo (string) config('app.env') === 'local' || file_exists($envBackupFileWhileStresstest);
    }

}

<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;


use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
class StresstestCache extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'stresstest:cache';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Prepare for stresstest cach';

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
        $this->info('Start caching');

        foreach(['config','route','view'] as $type){
            $this->printSubItem($type.' cache');
            Artisan::call(sprintf('%s:cache',$type));
            $this->info('done');
        };
        $this->info('caching complete');

    }

    protected function printSubItem($message){
        $this->output->write('<info>  o '.$message.'...</info>',false);
    }
}

<?php

namespace tcCore\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use tcCore\Log;

class duplicateTestTake extends Command
{

    public $data;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'testtake:duplicate {--silent : whether or not to show output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'collect all data from a testtake and serialize';

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
     * Execute the command.
     *
     * @return void
     */
    public function handle()
    {
        $this->data = collect([]);
        $start = microtime(true);
        $id = $this->argument('id');
        if(!$id){
            $this->error('we need an id to collect the data');
            exit;
        }



        if(!$this->option('silent')) $this->info(sprintf('going to clear all the records older than %d days',$days));
        if(!$this->option('silent')) $this->info('this can take some time');
        $date = new Carbon();
        $date->modify(sprintf('-%d days',$days));
        $formatted = $date->format('Y-m-d H:i:s');
        $rows = Log::where('created_at','<',$formatted)->delete();
        $duration = microtime(true) - $start;
        if(!$this->option('silent')) $this->info(sprintf('done, deleted %d records in %f seconds',$rows, $duration));
    }
}

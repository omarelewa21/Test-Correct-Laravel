<?php

namespace tcCore\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use tcCore\Log;

class ClearOldRequestLogs extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'requestlog:clear {days=5 : keep de last x days} {--silent : whether or not to show output}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'clear the old requestlogs';

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
        $days = $this->argument('days') ? (int) $this->argument('days') : 5;
        if(!$this->option('silent')) $this->info(sprintf('going to clear all the records older than %d days',$days));
        if(!$this->option('silent')) $this->info('this can take some time');
        $date = new Carbon();
        $date->modify(sprintf('-%d days',$days));
        $formatted = $date->format('Y-m-d H:i:s');
        $rows = Log::where('created_at','<',$formatted)->delete();
        if(!$this->option('silent')) $this->info(sprintf('done, %d records deleted',$rows));
    }
}

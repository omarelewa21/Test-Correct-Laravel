<?php

namespace tcCore\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Symfony\Component\Process\Process;

class CountOnlineUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:online';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Number of users online';

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
        $this->info(sprintf('%d users online',collect(DB::table('logs')->selectRaw('user_id')->whereRaw(' updated_at >= NOW() - INTERVAL 5 MINUTE')->groupBy('user_id')->get())->count()));
    }
}

<?php

namespace tcCore\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use tcCore\Log;
use tcCore\User;

class SetPasswordsForJonathan extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'devportal:pwJonathan';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'set passwords for Jonathan';

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
        if(substr_count(env('URL_LOGIN'),'devportal.test-correct') < 1){
            $this->error('You can not set the passwords other than in the devportal');
            exit;
        }
        User::all()->each->resetAndSavePassword('jonathan3456');
    }
}

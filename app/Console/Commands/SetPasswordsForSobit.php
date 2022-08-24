<?php

namespace tcCore\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use tcCore\Log;
use tcCore\User;

class SetPasswordsForSobit extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sobit:pw {--password=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'set passwords for Sobit';

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
        if(substr_count(env('URL_LOGIN'),'test-correct.test') < 1){
            $this->error('You can not set the passwords other than local');
            exit;
        }

        if($this->hasOption('password')){
            \DB::table('users')->update(['password' => Hash::make($this->option('password'))]);
            exit;
        }

        \DB::table('users')->update(['password' => '$2y$10$09COG9gAoSoOCG/PlzQw7ePKPX6xD6EkvOvz42H1vUiFAz5zXr.Aq']);
    }
}

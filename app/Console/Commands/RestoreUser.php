<?php

namespace tcCore\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use tcCore\Log;
use tcCore\User;

class RestoreUser extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:restore {user}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'restore deleted user';

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
        $id = $this->argument('user');
        if(User::find($id)){
            $this->error('the user isn\'t deleted');
            return;
        }
        $user = User::withTrashed()->find($id);
        if(!$user){
            $this->error('the user couldn\'t be found');
            return;
        }
        $user->restore();
        $this->info(sprintf('user %s %s %s restored',$user->name_first, $user->name_suffix, $user->name));
    }
}

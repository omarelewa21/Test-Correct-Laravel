<?php

namespace tcCore\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use tcCore\EckidUser;
use tcCore\Log;
use tcCore\SchoolLocation;
use tcCore\User;

class HotfixATMEckIds extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'atm:hotfix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'hotfix for atm eck ids';

    protected $schoolLocationId = 12;

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
        // all for school location id 14
        // find all t_ undeleted users and remove the teacher records and afterwards the t_ users
        // active teacher records should have the eckid_user record deleted
        // fix the eckIdHashes (both eckid Hash as well as the md5 hash) for 4 students based on userId
        if(!$this->confirm('Did you make a backup of the database?')){
            $this->error('Please do so first');
            return 1;
        }

        if(!$this->confirm('Is the school `'.SchoolLocation::find($this->schoolLocationId)->name.'` the correct one?')){
            $this->error('please fix the script first');
            return 1;
        }

        $testRun = true;
        if($this->confirm('We are in preview mode, do you want to skip preview mode?')){
            $testRun = false;
        }

        $this->info('Going to find all the teachers and delete them');
        $updateCount = 0;
        $deleteCount = 0;

        User::find(DB::table('school_location_user')->where('school_location_id',$this->schoolLocationId)->pluck('user_id'))->each(function(User $user) use (&$updateCount, &$deleteCount, $testRun){
            $this->info(sprintf('going to edit %s %s %s (%s) with id %d',$user->name_first, $user->name_suffix, $user->name, $user->username, $user->getKey()));
            if(!$user->trashed()) {
                if ($user->isA('teacher')) {
                    if(!$testRun) {
                        $user->external_id = '';
                        $user->save();
                    }
                    if ($user->hasImportMailAddress()) { // t_ users
                        $this->info('user has import address, so we delete the record');
                        if(!$testRun) {
                            $user->teacher()->delete();
                            $user->delete();
                        }
                        $deleteCount++;
                    } else { // regular users
                        if(!$testRun) {
                            $user->eckidFromRelation()->delete();
                        }
                        $this->info('eckid deleted');
                        $updateCount++;
                    }
                    $this->info('done');
                } else {
                    $this->error('this was not a teacher');
                }
            } else {
                $this->error('this user was already trashed');
            }
        });

        if($testRun){
            $this->error('This would have been happened if not in preview mode:');
        }
        $this->error(sprintf('Deleted Accounts %d, updated accounts %d',$deleteCount, $updateCount));
        if(!$testRun) {
            $this->info('we are all done!');
            $this->info('please check the data');
        }
    }
}

<?php

namespace tcCore\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use tcCore\EckidUser;
use tcCore\Log;
use tcCore\User;

class HotfixATCEckIds extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'atc:hotfix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'hotfix for atc eck ids';

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
        $this->info('Going to find all the teachers and delete them');
        $updateCount = 0;
        $deleteCount = 0;
        User::find(DB::table('school_location_user')->where('school_location_id',14)->pluck('user_id'))->each(function(User $user) use (&$updateCount, &$deleteCount){
            $this->info(sprintf('going to edit %s %s %s (%s) with id %d',$user->name_first, $user->name_suffix, $user->name, $user->username, $user->getKey()));
            if(!$user->trashed()) {
                if ($user->isA('teacher')) {
                    $user->external_id = '';
                    $user->save();
                    if ($user->hasImportMailAddress()) { // t_ users
                        $this->info('user has import address, so we delete the record');
                        $user->teacher()->delete();
                        $user->delete();
                        $deleteCount++;
                    } else { // regular users
                        $user->eckidFromRelation()->delete();
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

        $this->info('going to delete 4 student records');
        // delete student records for these ids
       User::find(['157562','158213','154418','172030'])->each(function(User $user){
           if($user->isA('student')) {
               $user->external_id = '';
               $user->save();
               $this->info(sprintf('going to delete %s %s %s', $user->name_first, $user->name_suffix, $user->name));
               $user->students()->delete();
               $user->delete();
               $this->info('done');
           } else {
            $this->error('this was not a student');
           }
       });

       $this->info('going to update 2 student records');
       collect([
           [
               'name' => 'Lisa',
               'id' => 64083,
               'eckId' => 'eyJpdiI6IkdjeTVOSC9xSmYvS0ZjdzhNdFMyQnc9PSIsInZhbHVlIjoiZW8yQWxuTnAwVXl2bUx3MFpaRFY0S0JnZW1rblU4UVE2R29Tb2RHdlBDTjd3MGd3elE1dkcrbnVTTjdSbGN2T1YzV3A3Z1pIR3JmR1k4Q3ZGK0UvcVkzUnZJdk51QTdza0JkakZBWlZNYkZXRUhaazBQYW4rZ0FMV1NjejU2VTFPWEFhR0JRc1BUYjZEUis5Q2RJRHVEckhFUGQvQTh5ZHJnWlFmcjZDSm55N095SDBQb1BQZDRveFRXUXRPT1FzQU9Sc25BY0VDNWcwa0lzREhwMHZtZz09IiwibWFjIjoiMmIzNDNjMzljMDA0NjJmZWZjY2FlNTA2NzM4YjdiNWMzOGE3YWE0NjBiZjc5MTc5YjliZjVhOWFhZjUyZDIyNyJ9',
               'md5Hash' => '9dcc6aea41e85486568324fdf8ab47ad',
           ],
           [
               'name' => 'Rick',
               'id' => 63999,
               'eckId' => 'eyJpdiI6InlxdjdpMDJQSS9pbWJvRDR0WDFuOFE9PSIsInZhbHVlIjoiaFNLdStpcmExSFo3MmRkVHEydE5iRkRqUTJuVHpTLzVNWUI5RHlZR3d0bDEyNzFWZStORGI1RUtaMDI2Y05FK1p4WTRIUWZ2VnM2WTlpcmIrOW0wTDF3TlVPTnFMU0hUVnF0UlNkSURBMjRtMk9NVFJrYUk0Zm02aVRZajJhMUVxY1ExU28wU2RhSHpWVThpYWFxRmVoTmU1c2sxcmtvY0laWGZFcW1aTURHK1hLblJTWHZ0U1BzVHN4VmdmQUFrY2RBRG5aZDV0VkJKUklnamszbHNPZz09IiwibWFjIjoiMTRkMzIzODkwNWFjMTFiNWM2Mzg1M2FlNWFlNGIzODYxNGZmZWE4NTVhZTAzNjBiY2UzZGJmZTI5OTE1YjkxNSJ9',
               'md5Hash' => 'd3cea44f36dbd916489c2e50b4aadc4e',
           ]
       ])->each(function($data){
           $obj = (object) $data;
           $this->info(sprintf('going to handle %s',$obj->name));
           $eckIdUser = EckidUser::where('user_id',$obj->id)->first();
           if($eckIdUser){
               $eckIdUser->eckid = $obj->eckId;
               $eckIdUser->eckid_hash = $obj->md5Hash;
               $eckIdUser->save();
           }
           $this->info('done');
       });
        $this->error(sprintf('Deleted Accounts %d, updated accounts %d',$deleteCount, $updateCount));
        $this->info('we are all done!');
        $this->info('please check the data');
    }
}

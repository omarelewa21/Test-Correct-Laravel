<?php

namespace tcCore\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use tcCore\Http\Helpers\ActingAsHelper;
use tcCore\Http\Helpers\BaseHelper;
use tcCore\Http\Helpers\ImportHelper;
use tcCore\Log;
use tcCore\SchoolLocation;
use tcCore\User;
use tcCore\UserRole;
use tcCore\UwlrSoapResult;

class ObfuscateUsernames extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users:obfuscate {ids} {leaveAloneIds}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'obfuscate usernames and names for non given ids for school locations';

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
        if(BaseHelper::onProduction()){
            die('Sorry, but this can not be run on live');
        }
        $ids = $this->argument('ids');
        if(Str::length($ids) < 1){
            die('Sorry, but we need the ids to keep out');
        }
        $ids = explode(',',$ids);

        $leaveAloneIds = $this->argument('leaveAloneIds');

        $leaveAloneIds = explode(',',$leaveAloneIds);

//        $sqlStudents = 'Update users set username = CONCAT("s_",users.id,"@test-correct.nl"),name_first = "s", name=CONCAT(users.id) where users.id IN (select user_id from user_roles where role_id = 3) AND school_location_id not in ()';
//        $sqlTeachers = 'Update users set username = CONCAT("t_",users.id,"@test-correct.nl"),name_first = "t", name=CONCAT(users.id) where users.id IN (select user_id from user_roles where role_id = 3)';

        $this->info('going to update students for the non needed locations');
        // students other locations
        User::leftJoin('user_roles','users.id','=','user_roles.user_id')
            ->where('user_roles.role_id',3)
            ->whereNotIn('school_location_id',$ids)
            ->update([
                'username' => DB::raw(" CONCAT('s_',users.id,'@test-correct.nl') "),
                'name_first' => 's',
                'name' => DB::raw(" CONCAT(users.id) ")
            ]);
        $this->comment('done');

        $this->info('going to update teachers for the non needed locations');
        // teachers other locations
        User::leftJoin('user_roles','users.id','=','user_roles.user_id')
            ->where('user_roles.role_id',1)
            ->whereNotIn('school_location_id',$ids)
            ->update([
                'username' => DB::raw(" CONCAT('t_',users.id,'@test-correct.nl') "),
                'name_first' => 't',
                'name' => DB::raw(" CONCAT(users.id) ")
            ]);
        $this->comment('done');

        $this->info('going to update all other usersfor the non needed locations');
        // students other locations
        User::leftJoin('user_roles','users.id','=','user_roles.user_id')
            ->whereNotIn('user_roles.role_id',[3,1])
            ->whereNotIn('school_location_id',$ids)
            ->update([
                'username' => DB::raw(" CONCAT('o_',users.id,'@test-correct.nl') "),
                'name_first' => 'o',
                'name' => DB::raw(" CONCAT(users.id) ")
            ]);
        $this->comment('done');

        $this->info('going to update students for the needed locations');
        // students needed locations
        User::leftJoin('user_roles','users.id','=','user_roles.user_id')
            ->where('user_roles.role_id',3)
            ->whereIn('school_location_id',$ids)
            ->whereNotIn('school_location_id',$leaveAloneIds)
            ->update([
                'username' => DB::raw(" CONCAT('s_',users.id,'@test-correct.nl') "),
                'name_first' => DB::raw(" CONCAT('OUD ',users.name_first) "),
                'name' => DB::raw(" CONCAT('OUD ',users.name) ")
            ]);
        $this->comment('done');

        $this->info('going to update teachers for the needed locations');
        // teachers needed locations
        User::leftJoin('user_roles','users.id','=','user_roles.user_id')
            ->where('user_roles.role_id',1)
            ->whereIn('school_location_id',$ids)
            ->whereNotIn('school_location_id',$leaveAloneIds)
            ->update([
                'username' => DB::raw(" CONCAT('t_',users.id,'@test-correct.nl') "),
                'name_first' => DB::raw(" CONCAT('OUD ',users.name_first) "),
                'name' => DB::raw(" CONCAT('OUD ',users.name) ")
            ]);
        $this->comment('done');

        $this->info('update all passwords');
        User::whereNotNull('id')->whereNotIn('school_location_id',$leaveAloneIds)->update(['password' =>'$2y$10$c47zbj2wJschPIq.rWPMAuOJyV4jjO0CYoeDshdIHWslv4ofA3Vvm']);
        $this->comment('done');

        return 0;

    }
}

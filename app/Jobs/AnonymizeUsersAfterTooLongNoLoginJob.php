<?php

namespace tcCore\Jobs;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\UmbrellaOrganization;
use tcCore\User;

class AnonymizeUsersAfterTooLongNoLoginJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;
    /**
     * @var User
     */
    protected $days = 365*2;

    /**
     * Create a new job instance.
     *
     * @param User $user
     * @return void
     */
    public function __construct($days = null)
    {
        if($days) {
            $this->days = $days;
        }
    }

    public function __invoke() {
        $this->handle();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $day = Carbon::today()->subDays($this->days);

        User::leftJoinSub(
                \DB::table('login_logs')
                    ->select('user_id',\DB::raw('max(login_logs.created_at) as max_created_at'))
                    ->groupBy('user_id'),
                'login_logs_alias',
                function($join){
                    $join->on('users.id','=','login_logs_alias.user_id');
                })
                ->where('username','not like','%test-correct.nl')
                ->where('username','not like','%testcorrect.nl')
                ->where(function($q) use ($day){
                    $q->where(function($query) use ($day) {
                        $query->whereNull('login_logs_alias.user_id')
                            ->where('users.created_at', '<', $day);
                    })
                   ->orWhere('login_logs_alias.max_created_at','<',$day);
            })->get()->each(function(User $user){
               $user->username = sprintf('%s-vervallenivm%ddagengeenlogin@test-correct.nl',$user->getKey(),$this->days);
               $user->password = '';
               $user->name_first = sprintf('%d',$this->days);
               $user->name_suffix = '';
               $user->name = sprintf('dagen => vervallen');
               $user->eckid = '';
               $user->save();
            });

    }
}

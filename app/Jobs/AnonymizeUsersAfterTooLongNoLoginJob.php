<?php

namespace tcCore\Jobs;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\FeatureSetting;
use tcCore\Message;
use tcCore\MessageReceiver;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\SearchFilter;
use tcCore\Text2Speech;
use tcCore\Text2SpeechLog;
use tcCore\UmbrellaOrganization;
use tcCore\User;
use tcCore\UserFeatureSetting;
use tcCore\UserSystemSetting;

class AnonymizeUsersAfterTooLongNoLoginJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    /**
     * @var User
     */
    protected $days = 365 * 2;
    protected $fieldsToEmpty = ['password', 'name_suffix', 'eckid', 'session_hash', 'api_key', 'gender', 'external_id',
        'invited_by'];
    protected $fieldsToNull = ['invited_by', 'profile_image_name', 'profile_image_size', 'profile_image_mime_type',
        'profile_image_extension'];
    protected $anonymisedUserIds = [];


    /**
     * Create a new job instance.
     *
     * @param User $user
     * @return void
     */
    public function __construct($days = null)
    {
        if ($days) {
            $this->days = $days;
        }
    }

    public function __invoke()
    {
        $this->handle();
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        DB::beginTransaction();
        try {
            $day = Carbon::today()->subDays($this->days);
            User::leftJoinSub(
                DB::table('login_logs')
                    ->select('user_id', DB::raw('max(login_logs.created_at) as max_created_at'))
                    ->groupBy('user_id'),
                'login_logs_alias',
                function ($join) {
                    $join->on('users.id', '=', 'login_logs_alias.user_id');
                })
                ->where(function($q){
                    $q->where('username', 'not like', '%test-correct.nl')
                    ->orWhere('guest',1); // also delete guest users after two years
                })
                ->where('username', 'not like', '%testcorrect.nl') // without the dash
                ->where(function ($q) use ($day) {
                    $q->where(function ($query) use ($day) {
                        $query->whereNull('login_logs_alias.user_id')
                            ->where('users.created_at', '<', $day);
                    })
                        ->orWhere('login_logs_alias.max_created_at', '<', $day);
                })->get()->each(function (User $user) {
                    $user->username = sprintf('%s-vervallenivm%ddagengeenlogin@test-correct.nl', $user->getKey(), $this->days);
                    $user->name_first = sprintf('%d', $this->days);
                    $roleName = match(true){
                        $user->isA('teacher') => 'teacher',
                        $user->isA('student') => 'student',
                        default => 'user',
                    };
                    $user->name =sprintf('former %s',$roleName) ;
                    $user->time_dispensation = false;
                    $user->text2speech = false;
                    foreach ($this->fieldsToEmpty as $field) {
                        $user->$field = '';
                    }
                    foreach ($this->fieldsToNull as $field) {
                        $user->$field = null;
                    }
                    $user->save();
                    $this->anonymisedUserIds[] = $user->getKey();
                    $user->delete();
                });

            if (count($this->anonymisedUserIds)) {
                // delete search filters
                SearchFilter::whereIn('user_id', $this->anonymisedUserIds)->delete();

                // delete text2speech records
                Text2Speech::whereIn('user_id',$this->anonymisedUserIds)->forceDelete();
                Text2SpeechLog::whereIn('user_id',$this->anonymisedUserIds)->delete();

                // delete user_system_settings
                UserSystemSetting::whereIn('user_id',$this->anonymisedUserIds)->forceDelete();

                // delete feature_settings
                FeatureSetting::whereIn('settingable_id',$this->anonymisedUserIds)->where('settingable_type',User::class)->delete();

                // delete user feature setting
                UserFeatureSetting::whereIn('user_id',$this->anonymisedUserIds)->forceDelete();

                // delete all the messages for these users
//                Message::whereIn('user_id',$this->anonymisedUserIds)->forceDelete();
//                MessageReceiver::whereIn('user_id',$this->anonymisedUserIds)->forceDelete();
            }


            DB::commit();
        } catch (\Throwable $e){
            DB::rollback();
            dump($e->getMessage());
        }

    }
}

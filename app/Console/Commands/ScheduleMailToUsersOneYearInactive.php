<?php

namespace tcCore\Console\Commands;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use mysql_xdevapi\Statement;
use tcCore\Jobs\FailedJob;
use tcCore\Jobs\SendInactiveUserMail;

class ScheduleMailToUsersOneYearInactive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'users_one_year_inactive:scheduled_mail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks and sends email to user who haven\'t been active for a year.' ;

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

        $arrayOfInactiveUsers = DB::select("
        SELECT us.id, us.username
        FROM users AS us
        INNER JOIN user_roles AS ur ON (us.id=ur.user_id)
        LEFT JOIN (
             SELECT MAX(ll.created_at) AS date_created, ll.user_id
             FROM login_logs AS ll
             GROUP BY ll.user_id
        ) AS ll
        ON (us.id=ll.user_id)
        LEFT JOIN (
             SELECT created_at, ms.user_id, ms.mailable
             FROM mails_send AS ms
        ) AS ms
        ON (us.id=ms.user_id)
        WHERE us.created_at > NOW() - INTERVAL 2 YEAR
        AND (
              ll.date_created < NOW() - INTERVAL 1 YEAR
              OR ( ll.date_created IS NULL 
                AND us.created_at < NOW() - INTERVAL 1 YEAR)
            )
        AND ur.role_id = 1
        AND (ms.mailable = '".SendInactiveUserMail::class."' 
             OR ms.created_at IS NULL)");


        if (count($arrayOfInactiveUsers) === 0){
            return $this->info('success: No year long inactive users found.');
        }

        foreach ($arrayOfInactiveUsers as $inactiveUser){
            try {
                Mail::to($inactiveUser->username)->queue(new SendInactiveUserMail($inactiveUser->id));
            } catch (\Throwable $th) {
                Bugsnag::notifyException($th);
                logger('failed'.$th);
                return $this->error($th->getMessage());
            }
        }

        return $this->info('success: this many users were being inactive at least a year: '.count($arrayOfInactiveUsers). ', they have been notified.' );

    }
}

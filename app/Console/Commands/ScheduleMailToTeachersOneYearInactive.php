<?php

namespace tcCore\Console\Commands;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use mysql_xdevapi\Statement;
use tcCore\Jobs\FailedJob;
use tcCore\Jobs\SendInactiveUserMail;
use tcCore\User;

class ScheduleMailToTeachersOneYearInactive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'teachers_one_year_inactive:scheduled_mail';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks and sends email to user who haven\'t been active for a year.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function getListOfInactiveTeachers()
    {
        return DB::select("
        SELECT us.id,us.username
        FROM users AS us
        LEFT JOIN user_roles AS ur ON (us.id=ur.user_id)
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
        LEFT JOIN (
             SELECT sl.activated, sl.id
             FROM school_locations AS sl
        ) AS sl
        ON (us.school_location_id=sl.id)
        WHERE 
            (us.created_at > NOW() - INTERVAL 2 YEAR
        AND us.created_at < NOW() - INTERVAL 1 YEAR)
        AND  
            (
                  (ll.date_created < NOW() - INTERVAL 1 YEAR
            AND    ll.date_created > NOW() - INTERVAL 2 YEAR)
            OR  ll.date_created IS NULL 
            )
        AND ur.role_id = 1
        AND sl.activated = 1
        AND (
                us.username NOT LIKE '%teachandlearncompany.com'
                AND us.username NOT LIKE '%test-correct.nl'
            ) 
        AND (
                ms.mailable = '" . SendInactiveUserMail::class . "' 
                OR ms.created_at IS NULL
             )
             ");
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $listOfInactiveTeachers = $this->getListOfInactiveTeachers();

        if (count($listOfInactiveTeachers) === 0) {
            return $this->info('success: No year long inactive users found.');
        }

        foreach ($listOfInactiveTeachers as $inactiveTeacher) {
            try {
                Mail::to($inactiveTeacher->username)->queue(new SendInactiveUserMail($inactiveTeacher->id));
            } catch (\Throwable $th) {
                Bugsnag::notifyException($th);
                return $this->error($th->getMessage());
            }
        }

        return $this->info('success: this many users were being inactive at least a year: ' . count($listOfInactiveTeachers) . ', they have been notified.');
    }


}

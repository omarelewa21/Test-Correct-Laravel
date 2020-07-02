<?php

namespace tcCore\Jobs;

use Illuminate\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;
use tcCore\Lib\User\Factory;
use tcCore\Lib\User\Roles;
use tcCore\User;

class SendWelcomeMail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $userId;
    protected $url;
    protected $key;

    /**
     * Create a new job instance.
     *
     * @param $userId
     * @param $url
     * @return void
     */
    public function __construct($userId, $url = '')
    {
        $this->key = Str::random(5);
        $this->userId = $userId;
        /** @TODO this var should be removed because it is not used MF 9-6-2020 */
        $this->url = $url;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Mailer $mailer)
    {
        $user = User::findOrFail($this->userId);
        $user->setAttribute('send_welcome_email', true);
        $factory = new Factory($user);
        $password = $factory->generateNewPassword();

        $roles = Roles::getUserRoles($user);
        if (in_array('Student', $roles) && count($roles) === 1) {
            $template = 'emails.welcome.student';
        } else {

            //TC-145
            if ($user->invited_by != null) {
                $template = 'emails.welcome.invitebywelcome-staff';
            } else {
                $template = 'emails.welcome.staff';
            }

        }
        $mailer->send($template, ['user' => $user, 'url' => $this->url, 'password' => $password], function ($m) use ($user) {
            $m->to($user->getEmailForPasswordReset())->subject('Welkom in Test-Correct');
        });

        $user->save();
    }
}

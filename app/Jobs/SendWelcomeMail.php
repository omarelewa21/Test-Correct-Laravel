<?php

namespace tcCore\Jobs;

use Illuminate\Mail\Mailer;
use tcCore\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\Lib\User\Factory;
use tcCore\Lib\User\Roles;
use tcCore\User;

class SendWelcomeMail extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $userId;
    protected $url;

    /**
     * Create a new job instance.
     *
     * @param $userId
     * @param $url
     * @return void
     */
    public function __construct($userId, $url)
    {
        $this->userId = $userId;
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
            $template = 'emails.welcome.staff';
        }

        $mailer->send($template, ['user' => $user, 'url' => $this->url, 'password' => $password], function ($m) use ($user) {
            $m->to($user->getEmailForPasswordReset())->subject('Welkom in Test-Correct');
        });


        $user->save();
    }
}

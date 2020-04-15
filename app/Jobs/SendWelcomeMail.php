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

    /**
     * Create a new job instance.
     *
     * @param $userId
     * @param $url
     * @return void
     */
    public function __construct($userId, $url)
    {
        $this->key = Str::random(5);
        logger('=== START WELCOME MAIL ('.$this->key.') ===');
        logger($this->key.' adding welcome mail for '.$userId);
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
        logger($this->key.' Start handling');
        $user = User::findOrFail($this->userId);
        logger($this->key.' user found '.$user->username);
        $user->setAttribute('send_welcome_email', true);
        $factory = new Factory($user);
        $password = $factory->generateNewPassword();

        $roles = Roles::getUserRoles($user);
        if (in_array('Student', $roles) && count($roles) === 1) {
            $template = 'emails.welcome.student';
        } else {
            $template = 'emails.welcome.staff';
        }
        logger($this->key.' template '.$template);
        $mailer->send($template, ['user' => $user, 'url' => $this->url, 'password' => $password], function ($m) use ($user) {
            $m->to($user->getEmailForPasswordReset())->subject('Welkom in Test-Correct');
        });

        logger($this->key.' message has been sent');

        $user->save();

        logger('### END WELCOME MAIL ('.$this->key.') ###');
    }
}

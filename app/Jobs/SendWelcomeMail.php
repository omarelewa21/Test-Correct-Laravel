<?php

namespace tcCore\Jobs;

use Illuminate\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;
use tcCore\EmailConfirmation;
use tcCore\Lib\User\Factory;
use tcCore\Lib\User\Roles;
use tcCore\User;

class SendWelcomeMail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $userId;
    protected $url;
    protected $key;


    public $testBody;

    /**
     * Create a new job instance.
     *
     * @param $userId
     * @param $url
     * @return void
     */
    public function __construct($userId, $url = '')
    {
        $this->queue = 'mail';
        $this->key = Str::random(5);
        $this->userId = $userId;



    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Mailer $mailer)
    {
        $user = User::findOrFail($this->userId);
        // should never mail import users with t_ or s_ @test-correct.nl
        if($user->shouldNotSendMail()) {
            return;
        }
        $user->setAttribute('send_welcome_email', true);
        $factory = new Factory($user);
        //$password = $factory->generateNewPassword();

        $emailConfirmation = EmailConfirmation::create(
            ['user_id' => $user->getKey()]
        );

        $this->url =  sprintf('%spassword-reset/?token=%s',config('app.base_url'), $emailConfirmation->uuid);


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
        $stub = '';
        $mailer->send($template, ['user' => $user, 'url' => $this->url], function ($m) use ($user, &$stub) {
            $m->to($user->getEmailForPasswordReset())->subject('Welkom in Test-Correct');
            $stub = $m;
        });


        $user->save();
        $this->testBody = $stub->getBody();
    }
}

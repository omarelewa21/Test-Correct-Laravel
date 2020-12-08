<?php

namespace tcCore\Jobs;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;
use tcCore\Lib\User\Factory;
use tcCore\Lib\User\Roles;
use tcCore\User;

class SendOnboardingWelcomeMail extends Mailable implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $userId;
    protected $url;
    protected $key;
    public $user;

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
        $this->user = User::findOrFail($this->userId);

        /** @TODO this var should be removed because it is not used MF 9-6-2020 */
        $this->url = $url;

        $this->user->setAttribute('send_welcome_email', true);
        $this->user->save();
    }

    public function build()
    {
        return $this->view('emails.welcome.onboarding-welcome');
    }
}

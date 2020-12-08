<?php

namespace tcCore\Jobs;

use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Str;
use Ramsey\Uuid\Uuid;
use tcCore\EmailConfirmation;
use tcCore\Http\Livewire\Onboarding;
use tcCore\Lib\User\Factory;
use tcCore\Lib\User\Roles;
use tcCore\User;

class SendOnboardingWelcomeMail extends Mailable implements ShouldQueue
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

    public function handle(Mailer $mailer)
    {
        $user = User::findOrFail($this->userId);
        $user->setAttribute('send_welcome_email', true);
        $user->save();
        $emailConfirmation = EmailConfirmation::create(
            ['user_id' => $user->getKey()]
        );

        $template = 'emails.welcome.onboarding-welcome';
        $mailer->send($template, ['user' => $user, 'url' => $this->url, 'token' => $emailConfirmation->uuid], function ($m) use ($user) {
            $m->to($user->getEmailForPasswordReset())->subject('Welkom in Test-Correct');
        });
    }

    public function build()
    {
        return $this->view('emails.welcome.onboarding-welcome');
    }
}

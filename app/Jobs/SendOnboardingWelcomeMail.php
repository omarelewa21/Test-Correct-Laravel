<?php

namespace tcCore\Jobs;

use Illuminate\Bus\Queueable;
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

class SendOnboardingWelcomeMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    protected $url;
    protected $key;
    protected $skipVerificationPart = false;

    /**
     * Create a new job instance.
     *
     * @param $userId
     * @param $url
     * @return void
     */
    public function __construct(User $user, $url = '', $skipVerificationPart = false)
    {
        $this->queue = 'mail';
        $this->key = Str::random(5);
        $this->user = $user;
        /** @TODO this var should be removed because it is not used MF 9-6-2020 */
        $this->url = $url;
        $this->skipVerificationPart = $skipVerificationPart;
    }

    public function render()
    {
        $this->user->fresh();
        if(is_null($this->user)||!is_null($this->user->deleted_at)){
            return false;
        }
        parent::render();
    }

    public function build()
    {
        $this->user->setAttribute('send_welcome_email', true);
        $this->user->save();
        if(!$this->skipVerificationPart) {
            $emailConfirmation = EmailConfirmation::create(
                ['user_id' => $this->user->getKey()]
            );
        }
        return $this->view('emails.welcome.onboarding-welcome')
            ->subject('Welkom bij Test-Correct')
            ->with([
                'user' => $this->user,
                'url' => $this->url,
                'token' => (!$this->skipVerificationPart) ? $emailConfirmation->uuid : null,
                'skipVerificationPart' => $this->skipVerificationPart
            ]);
    }
}

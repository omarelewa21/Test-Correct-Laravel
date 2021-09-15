<?php

namespace tcCore\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use tcCore\User;

class SendForgotPasswordMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $user;
    protected $token;
    protected $url;
    protected $urlLogin;
    public $queue = 'mail';

    public function __construct(User $user, $token, $url, $urlLogin)
    {
        $this->user = $user;
        $this->token = $token;
        $this->url = $url;
        $this->urlLogin = $urlLogin;
    }

    public function build()
    {
        return $this->view('emails.password')
            ->subject('Nieuw wachtwoord aangevraagd.')
            ->with([
                'user' => $this->user,
                'token' => $this->token,
                'url' => $this->url,
                'urlLogin' => $this->urlLogin,
            ]);
    }
}

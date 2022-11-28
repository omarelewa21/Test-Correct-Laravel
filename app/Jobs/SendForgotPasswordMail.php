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

    public function __construct(User $user, $token, $url, $urlLogin)
    {
        $this->queue = 'mail';
        $this->user = $user;
        $this->token = $token;
        $this->url = $url;
        $this->urlLogin = $urlLogin;
    }

    public function build()
    {
        return $this->view('emails.password')
            ->subject('Wachtwoord opnieuw instellen aangevraagd')
            ->with([
                'user' => $this->user,
                'token' => $this->token,
                'url' => $this->url,
                'urlLogin' => $this->urlLogin,
            ]);
    }
}

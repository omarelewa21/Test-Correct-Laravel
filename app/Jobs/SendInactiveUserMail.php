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
use tcCore\User;

class SendInactiveUserMail extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    protected int $userId;

    /**
     * Create a new job instance.
     *
     * @param $userId
     * @return void
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    public function build()
    {
        $this->user = User::find($this->userId);

        return $this->view('emails.inactive_user_mail')
            ->subject('Actie vereist om jouw account te behouden')
            ->with([
                'user' => $this->user
            ]);
    }
}

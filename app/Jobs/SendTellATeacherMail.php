<?php

namespace tcCore\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use tcCore\EmailConfirmation;
use tcCore\User;

class SendTellATeacherMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $inviter;

    /**
     * Create a new job instance.
     *
     * @param $userId
     * @param $url
     * @return void
     */
    public function __construct(User $inviter)
    {
        $this->inviter = $inviter;

    }

    public function build()
    {
        return $this->view('emails.tell-a-teacher')
            ->subject($this->inviter->username.' heeft je uitgenodigd voor Test-Correct')
            ->with([
                'user' => $this->user, 'url' => $this->url, 'token' => $emailConfirmation->uuid
            ]);
    }
}

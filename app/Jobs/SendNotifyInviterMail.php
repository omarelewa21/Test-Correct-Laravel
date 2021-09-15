<?php

namespace tcCore\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use tcCore\EmailConfirmation;
use tcCore\Lib\Models\StiBaseModel;
use tcCore\User;

class SendNotifyInviterMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $inviter;
    protected $invitee;
    public $queue = 'mail';

    /**
     * Create a new job instance.
     *
     * @param $userId
     * @param $url
     * @return void
     */
    public function __construct(User $inviter, User $invitee)
    {
        $this->inviter = $inviter;
        $this->invitee = $invitee;
    }

    public function build()
    {
        return $this->view('emails.notify_inviter')
            ->subject('Je collega '.$this->invitee->getNameFullAttribute().' heeft zich geregistreerd bij Test-Correct')
            ->with([
                'inviter' => $this->inviter,
                'invitee' => $this->invitee,
            ]);
    }
}

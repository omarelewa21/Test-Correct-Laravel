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

    /**
     * Create a new job instance.
     *
     * @param $userId
     * @param $url
     * @return void
     */
    public function __construct(User $inviter, User $invitee)
    {
        $this->queue = 'mail';
        $this->inviter = $inviter;
        $this->invitee = $invitee;
    }

    public function render()
    {
        $this->invitee->fresh();
        $this->inviter->fresh();
        if(is_null($this->invitee)||is_null($this->inviter)||!is_null($this->invitee->deleted_at)||!is_null($this->inviter->deleted_at)){
            return false;
        }
        parent::render();
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

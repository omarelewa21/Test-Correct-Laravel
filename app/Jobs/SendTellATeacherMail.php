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
    protected $inviteText;

    /**
     * Create a new job instance.
     *
     * @param $userId
     * @param $url
     * @return void
     */
    public function __construct(User $inviter, $inviteText)
    {
        $this->inviter = $inviter;
        $this->inviteText = $inviteText;
    }

    public function build()
    {
        if ($this->inviter->name_suffix) {
            $fullname = $this->inviter->name_first.' '.$this->inviter->name_suffix.' '.$this->inviter->name;
        } else {
            $fullname = $this->inviter->name_first.' '.$this->inviter->name;
        }
        logger($this->inviteText);
        return $this->view('emails.tell-a-teacher')
            ->subject('Je collega '.$fullname.' heeft je uitgenodigd voor Test-Correct')
            ->with([
                'inviter' => $fullname,
                'inviteText' => $this->inviteText
            ]);
    }
}

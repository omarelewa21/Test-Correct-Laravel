<?php

namespace tcCore\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Str;
use tcCore\EmailConfirmation;
use tcCore\Lib\Models\StiBaseModel;
use tcCore\User;

class SendTellATeacherMail extends Mailable
{
    use Queueable, SerializesModels;

    protected $inviter;
    protected $inviteText;
    protected $receivingEmailAddress;
    protected $shortcode;


    /**
     * Create a new job instance.
     *
     * @param $userId
     * @param $url
     * @return void
     */
    public function __construct(User $inviter, $inviteText, $receivingEmailAddress, $shortcode)
    {
        $this->queue = 'mail';
        $this->inviter = $inviter;
        $this->inviteText = $inviteText;
        $this->receivingEmailAddress = $receivingEmailAddress;
        $this->shortcode = $shortcode;
    }

    public function render()
    {
        $this->inviter->fresh();
        if(is_null($this->inviter)||!is_null($this->inviter->deleted_at)){
            return false;
        }
        parent::render();
    }

    public function build()
    {
        if ($this->inviter->name_suffix) {
            $fullname = $this->inviter->name_first.' '.$this->inviter->name_suffix.' '.$this->inviter->name;
        } else {
            $fullname = $this->inviter->name_first.' '.$this->inviter->name;
        }
        return $this->view('emails.tell-a-teacher')
            ->subject('Je collega '.$fullname.' heeft je uitgenodigd voor Test-Correct')
            ->with([
                'inviter' => $fullname,
                'inviteText' => $this->inviteText,
                'receivingEmailAddress' => $this->receivingEmailAddress,
                'shortcode' => $this->shortcode,
            ]);
    }
}

<?php

namespace tcCore\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class SendSamlNoMailAddressInRequestDetectedMail extends Mailable
{
    use Queueable, SerializesModels;

    public $schoolName;
    public $timeDetected;
    public $subject;
    public $attr;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($schoolName, $subject, $attr = [])
    {
        $this->schoolName = $schoolName;
        $this->timeDetected = now();
        $this->subject = $subject;
        $this->attr = $attr;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.saml_no_mailaddress_in_request_detected')
            ->subject($this->subject);
    }
}

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

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($schoolName)
    {
        $this->schoolName = $schoolName;
        $this->timeDetected = now();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.saml_no_mailaddress_in_request_detected');
    }
}

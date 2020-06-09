<?php

namespace tcCore\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\DemoTeacherRegistration;

class TeacherRegistered extends Mailable
{
    use Queueable, SerializesModels;

    public $demo;
    public $withDuplicatedEmailAddress;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(DemoTeacherRegistration $registration, $withDuplicateEmailAddress =  false)
    {
        $this->demo = $registration;
        $this->withDuplicateEmailAddress = $withDuplicateEmailAddress;
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.teacher_registered');//->from($this->demo->username);
    }
}

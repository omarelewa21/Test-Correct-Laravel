<?php

namespace tcCore\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\DemoTeacherRegistration;

class TeacherInTestSchoolTriesToUpload extends Mailable
{
    use Queueable, SerializesModels;

    public $demo;
    public $withDuplicateEmailAddress;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(DemoTeacherRegistration $registration)
    {
        $this->demo = $registration;
        //
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.teacher_in_test_school_tries_to_upload');//->from($this->demo->username);
    }
}

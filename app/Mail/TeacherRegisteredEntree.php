<?php

namespace tcCore\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\School;
use tcCore\Subject;
use tcCore\User;

class TeacherRegisteredEntree extends Mailable
{
    use Queueable, SerializesModels;

    public object $user;

    public object $school;

    public object $subjects;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(User $user)
    {
        $this->user = $user;
        $this->subjects = $this->user->subjects()->get();
        $this->school = $this->user->schoolLocation()->get()->first()->school()->get()->first();
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->view('emails.teacher_registered_entree');//->from($this->demo->username);
    }
}

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

    public User $user;

    public object $schoolLocation;

    public object $subjects;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($userId)
    {
        $this->user = User::find($userId);
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->subjects = $this->user->subjects()->get();
        $this->schoolLocation = $this->user->schoolLocation()->get()->first();
        return $this->view('emails.teacher_registered_entree');//->from($this->demo->username);
    }
}

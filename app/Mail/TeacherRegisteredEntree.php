<?php

namespace tcCore\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\Subject;
use tcCore\User;

class TeacherRegisteredEntree extends Mailable
{
    use Queueable, SerializesModels;

    public User $user;

    public int $userId;

    public SchoolLocation $schoolLocation;

    public object $subjects;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($userId)
    {
        $this->userId = $userId;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $this->user = User::find($this->userId);
        $this->subjects = $this->user->subjects()->get();
        $this->schoolLocation = $this->user->schoolLocation;
        return $this->view('emails.teacher_registered_entree');
    }
}

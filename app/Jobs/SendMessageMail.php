<?php

namespace tcCore\Jobs;

use Illuminate\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\Lib\User\Roles;
use tcCore\Message;

class SendMessageMail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $messageId;

    /**
     * Create a new job instance.
     *
     * @param $messageId
     */
    public function __construct($messageId)
    {
        $this->messageId = $messageId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Mailer $mailer)
    {
        $message = Message::with('user', 'messageReceivers', 'messageReceivers.user')->findOrFail($this->messageId);

        foreach ($message->messageReceivers as $messageReceiver) {
            $email = $messageReceiver->user->getEmailForPasswordReset();
            $name = $messageReceiver->user->getNameFullAttribute();

            $roles = Roles::getUserRoles($messageReceiver->user);
            if (in_array('Student', $roles) && count($roles) === 1) {
                $template = 'emails.message.student';
            } else {
                $template = 'emails.message.staff';
            }

            $mailer->send($template, ['receiver' => $messageReceiver->user, 'sentMessage' => $message], function ($m) use ($email, $name, $message) {
                $m->to($email, $name);
                $m->subject($message->getAttribute('subject'));
            });
        }
    }
}

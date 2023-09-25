<?php

namespace tcCore\Jobs;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
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
        $this->queue = 'mail';
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
        $urlLogin = config('app.url_login');

        foreach ($message->messageReceivers as $messageReceiver) {
            if(null == $messageReceiver->user || $messageReceiver->user->shouldNotSendMail()) {
                continue;
            }
            $email = $messageReceiver->user->getEmailForPasswordReset();
            $name = $messageReceiver->user->getNameFullAttribute();

            $roles = Roles::getUserRoles($messageReceiver->user);
            if (in_array('Student', $roles) && count($roles) === 1) {
                $template = 'emails.message.student';
            } else {
                $template = 'emails.message.staff';
            }

            try {
                $mailer->send($template, ['receiver_name' => $name, 'receiver' => $messageReceiver->user, 'sentMessage' => $message, 'urlLogin' => $urlLogin], function ($m) use ($email, $name, $message) {
                    $m->to($email, $name);
                    $m->subject($message->getAttribute('subject'));
                });
            } catch (\Throwable $th) {
                Bugsnag::notifyException($th);
            }

        }
    }
}

<?php

namespace tcCore\Jobs;

use Illuminate\Mail\Mailer;
use tcCore\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\Lib\User\Factory;
use tcCore\Lib\User\Roles;
use tcCore\User;

class SendExceptionMail extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $errMessage;
    protected $file;
    protected $lineNr;
    protected $details;

    /**
     * Create a new job instance.
     *
     * @param $errMessage
     * @param $file
     * @param $lineNr
     * @param $details (array)
     * @return void
     */
    public function __construct($errMessage, $file, $lineNr, $details = [])
    {
        $this->errMessage = $errMessage;
        $this->file = $file;
        $this->lineNr = $lineNr;
        $this->details = $details;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Mailer $mailer)
    {
        $template = 'emails.exception';

        $serverDetails = $_SERVER;
        unset($serverDetails['MAIL_PASSWORD']);

        $mailer->send($template, [  'errMessage' => $this->errMessage,
                                    'file' => $this->file,
                                    'lineNr' => $this->lineNr,
                                    'details' => $this->details,
                                    'server' => $_SERVER], function ($m) {
            $m->to(
                config('mail.mail_dev_address')
            )->subject('test-correct exception');
        });
    }
}

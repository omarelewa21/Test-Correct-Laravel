<?php

namespace tcCore\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailer;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendExceptionMail extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $errMessage;
    protected $file;
    protected $lineNr;
    protected $details;

    protected $subject = 'test-correct exception';

    /**
     * Create a new job instance.
     *
     * @param $errMessage
     * @param $file
     * @param $lineNr
     * @param $details (array)
     * @return void
     */
    public function __construct($errMessage, $file, $lineNr, $details = [], $subject = null)
    {
        $this->queue = 'mail';
        $this->errMessage = $errMessage;
        $this->file = $file;
        $this->lineNr = $lineNr;
        $this->details = $details;
        if(null !== $subject) {
            $this->subject = $subject;
        }

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
        $ar = ['MAIL_PASSWORD','APP_KEY','DB_USERNAME','DB_PASSWORD','MAIL_FROM_ADDRESS'];
        foreach($ar as $a){
            unset($serverDetails[$a]);
        }


        $mailer->send($template, ['errMessage' => $this->errMessage,
                                  'file'       => $this->file,
                                  'lineNr'     => $this->lineNr,
                                  'details'    => $this->details,
                                  'server'     => $serverDetails], function ($m) {
            $m->to(config('mail.mail_dev_address'))
                ->cc(config('mail.from.address'))
                ->subject($this->subject);
        });
    }
}

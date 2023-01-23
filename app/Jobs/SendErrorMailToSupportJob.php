<?php

namespace tcCore\Jobs;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendErrorMailToSupportJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $error;
    protected $subject;
    protected $details;

    /**
     * Create a new job instance.
     *
     * @param $error
     * @param $subject
     */
    public function __construct($error, $subject, $details = [])
    {
        $this->error = $error;
        $this->subject = $subject;
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

            try {
                $mailer->send($template, ['receiver' => config('mail.from.address'), 'error' => $this->error, 'details' => $this->details], function ($m) {
                    $m->to(config('mail.from.address'),config('mail.from.name'));
                    $m->subject($this->subject);
                });
            } catch (\Throwable $th) {
                Bugsnag::notifyException($th);
            }
    }
}

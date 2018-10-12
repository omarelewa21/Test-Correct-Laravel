<?php

namespace tcCore\Jobs;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Mail\Mailer;
use tcCore\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\TestTake;

class SendTestRatedMail extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $testTake;

    /**
     * Create a new job instance.
     *
     * @param TestTake $testTake
     * @return void
     */
    public function __construct(TestTake $testTake)
    {
        $this->testTake = $testTake;
    }

    /**
     * Execute the job.
     *
     * @param Mailer $mailer
     * @return void
     */
    public function handle(Mailer $mailer)
    {
        $urlLogin = getenv('URL_LOGIN');
        if ($this->testTake->testTakeStatus->name === 'Rated') {
            foreach($this->testTake->testParticipants as $testParticipant) {
                if ($testParticipant->getAttribute('rating') === null) {
                    continue;
                }

                $mailer->send('emails.test_rated', compact('testParticipant', 'urlLogin'), function ($mail) use ($testParticipant) {
                    $mail->to($testParticipant->user->username, $testParticipant->user->getNameFullAttribute())->subject('Toets beoordeeld.');
                });
            }
        }
    }
}

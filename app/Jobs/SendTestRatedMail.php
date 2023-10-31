<?php

namespace tcCore\Jobs;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Carbon\Carbon;
use Illuminate\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\TestTake;

class SendTestRatedMail extends Job implements ShouldQueue
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
        $this->queue = 'mail';
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
        if ($this->testTake->testTakeStatus->name === 'Rated'
            && $this->testTake->review_active
            && $this->testTake->show_results > Carbon::now()) {
            foreach ($this->testTake->testParticipants as $testParticipant) {
                if(null == $testParticipant->user || $testParticipant->user->shouldNotSendMail()) {
                    continue;
                }
                if ($testParticipant->getAttribute('rating') === null) {
                    continue;
                }

                try {
                    $mailer->send('emails.test_rated', compact('testParticipant', 'urlLogin'), function ($mail) use ($testParticipant) {
                        $mail->to($testParticipant->user->username, $testParticipant->user->getNameFullAttribute())->subject('Toets beoordeeld.');
                    });
                } catch (\Throwable $th) {
                    Bugsnag::notifyException($th);
                }
            }
        }
    }
}

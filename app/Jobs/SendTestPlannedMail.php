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

class SendTestPlannedMail extends Job implements SelfHandling, ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $testTakeId;

    /**
     * Create a new job instance.
     *
     * @param $userId
     * @param $url
     * @return void
     */
    public function __construct($testTakeId)
    {
        $this->testTakeId = $testTakeId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Mailer $mailer)
    {
        try {
            $testTake = TestTake::findOrFail($this->testTakeId);
        } catch (ModelNotFoundException $e) {
            return;
        }

        if ($testTake->testTakeStatus->name === 'Planned') {
            foreach($testTake->testParticipants as $testParticipant) {
                $mailer->send('emails.test_planned', ['testParticipant' => $testParticipant], function ($mail) use ($testParticipant) {
                    $mail->to($testParticipant->user->username, $testParticipant->user->getNameFullAttribute())->subject('Toetsafname ingepland.');
                });
            }
        }
    }
}

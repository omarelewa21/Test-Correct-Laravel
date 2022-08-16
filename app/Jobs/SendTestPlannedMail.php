<?php

namespace tcCore\Jobs;

use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\TestKind;
use tcCore\TestTake;

class SendTestPlannedMail extends Job implements ShouldQueue
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
        $this->queue = 'mail';
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
            $testTake   = TestTake::findOrFail($this->testTakeId);
            $directlink = config('app.base_url') ."directlink/". $testTake->uuid;
        } catch (ModelNotFoundException $e) {
            return;
        }

        if ($testTake->testTakeStatus->name === 'Taking test' && $testTake->test->test_kind_id == TestKind::ASSESSMENT_TYPE) {
            foreach($testTake->testParticipants as $testParticipant) {
                if(null == $testParticipant->user || $testParticipant->user->shouldNotSendMail()) {
                    continue;
                }
                $mailer->send('emails.assignment_planned', ['testParticipant' => $testParticipant], function ($mail) use ($testParticipant) {
                    $mail->to($testParticipant->user->username, $testParticipant->user->getNameFullAttribute())->subject(__('assignment_planned.Opdracht ingepland.'));
                });
            }
        }

        if ($testTake->testTakeStatus->name === 'Planned') {
            foreach($testTake->testParticipants as $testParticipant) {
                if(null == $testParticipant->user || $testParticipant->user->shouldNotSendMail()) {
                    continue;
                }
                $mailer->send('emails.test_planned', ['testParticipant' => $testParticipant, 'directlink' => $directlink], function ($mail) use ($testParticipant) {
                    $mail->to($testParticipant->user->username, $testParticipant->user->getNameFullAttribute())->subject(__('test_planned.Toetsafname ingepland.'));
                });
            }
        }
    }
}

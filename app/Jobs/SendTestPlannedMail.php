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
            $takeCode = $testTake->testTakeCode;
            $is_assessment = $testTake->isAssessmentType();
            if($takeCode){
                $takeCode = $takeCode->prefix . '  ' . implode(' ', str_split($takeCode->code));
            }
        } catch (ModelNotFoundException $e) {
            return;
        }

        if ($testTake->testTakeStatus->name === 'Taking test' && $is_assessment) {
            foreach($testTake->testParticipants as $testParticipant) {
                if(null == $testParticipant->user || $testParticipant->user->shouldNotSendMail()) {
                    continue;
                }
                $mailer->send('emails.assignment_planned', ['testParticipant' => $testParticipant, 'directlink' => $testTake->directLink, 'takeCode' => $takeCode], function ($mail) use ($testParticipant) {
                    $mail->to($testParticipant->user->username, $testParticipant->user->getNameFullAttribute())->subject(__('assignment_planned.Opdracht ingepland.'));
                });
            }
        }

        if ($testTake->testTakeStatus->name === 'Planned') {
            // Send to students
            foreach($testTake->testParticipants as $testParticipant) {
                if(null == $testParticipant->user || $testParticipant->user->shouldNotSendMail()) {
                    continue;
                }
                $mailer->send('emails.test_planned',
                    ['testParticipant' => $testParticipant, 'directlink' => $testTake->directLink, 'takeCode' => $takeCode, 'is_assessment' => $is_assessment],
                    function ($mail) use ($testParticipant, $is_assessment) {
                        $mail->to($testParticipant->user->username, $testParticipant->user->getNameFullAttribute())->subject( $is_assessment ? __('test_planned.assignment_planned') : __('test_planned.Toetsafname ingepland.'));
                });
            }
            // Send to Invigilators
            foreach($testTake->invigilators as $invigilator){
                if($invigilator->user->username !== $testTake->user->username){
                    $mailer->send('emails.teacher_test_planned',
                        ['user' => $invigilator->user, 'testTake' => $testTake, 'directlink' => $testTake->directLink, 'is_invigilator' => true, 'takeCode' => $takeCode, 'is_assessment' => $is_assessment],
                        function ($mail) use ($invigilator, $is_assessment) {
                            $mail->to($invigilator->user->username, $invigilator->user->getNameFullAttribute())->subject($is_assessment ? __('test_planned.assignment_planned') : __('test_planned.Toetsafname ingepland.'));
                    });
                }
            }
            // Send to test owner
            $mailer->send('emails.teacher_test_planned',
                ['user' => $testTake->user, 'testTake' => $testTake, 'directlink' => $testTake->directLink, 'is_invigilator' => false, 'takeCode' => $takeCode],
                function ($mail) use ($testTake) {
                    $mail->to($testTake->user->username, $testTake->user->getNameFullAttribute())->subject(__('test_planned.Toetsafname ingepland.'));
            });
        }
    }
}

<?php

namespace tcCore\Jobs;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Carbon\Carbon;
use Illuminate\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use tcCore\FileManagement;
use tcCore\Lib\User\Factory;
use tcCore\Lib\User\Roles;
use tcCore\User;

class SendToetsenbakkerInviteMail extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    protected $fileManagementId;

    /**
     * Create a new job instance.
     *
     * @param $userId
     * @param $url
     * @return void
     */
    public function __construct($fileManagementId)
    {
        $this->queue = 'mail';
        $this->fileManagementId = $fileManagementId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Mailer $mailer)
    {
        $fileManagement = FileManagement::findOrFail($this->fileManagementId);

        $template = 'emails.toetsenbakker_toetsinvite';

        try {
            $mailer->send($template, ['fileManagement' => $fileManagement], function ($m) use($fileManagement) {
                $m->to($fileManagement->typedetails->invite)->subject('Test-Correct, uitnodiging om een toets te bakken');
            });
        } catch (\Throwable $th) {
            Bugsnag::notifyException($th);
        }


        // can't be set directly through $fileManagement->typedetails->invited_at = Carbon::now(); as it is a mutator
        $td = $fileManagement->typedetails;
        $td->invited_at = Carbon::now();
        $fileManagement->typedetails = $td;
        $fileManagement->save();
    }
}

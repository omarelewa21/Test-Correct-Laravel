<?php

namespace tcCore\Jobs;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Illuminate\Bus\Queueable;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Mail\Mailer;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class SendUwlrImportSchoolLocationSuccessToSupportJob extends Job implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $schoolLocationName;

    /**
     * Create a new job instance.
     *
     * @param $schoolLocation
     */
    public function __construct($schoolLocationName)
    {
        $this->schoolLocationName = $schoolLocationName;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle(Mailer $mailer)
    {

        $template = 'emails.uwlr_import_school_location_success';

        try {
            $mailer->send($template, ['receiver' => config('mail.from.address'), 'schoolLocationName' => $this->schoolLocationName], function ($m) {
                $m->to(config('mail.from.address'), config('mail.from.name'))
                    ->subject(sprintf('School locatie %s succesvol geimporteerd via uwlr',$this->schoolLocationName));
                });
        } catch (\Throwable $th) {
//            Bugsnag::notifyException($th);
        }
    }
}

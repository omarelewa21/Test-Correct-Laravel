<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 09/04/2020
 * Time: 10:59
 */

namespace tcCore\Http\Helpers;


use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use tcCore\Answer;
use tcCore\BaseSubject;
use tcCore\EducationLevel;
use tcCore\FailedLogin;
use tcCore\Jobs\ProcessUwlrSoapResultJob;
use tcCore\Jobs\SendWelcomeMail;
use tcCore\Jobs\SetSchoolYearForDemoClassToCurrent;
use tcCore\Lib\Repositories\PeriodRepository;
use tcCore\Lib\Repositories\SchoolYearRepository;
use tcCore\Lib\User\Factory;
use tcCore\Lib\User\Roles;
use tcCore\LoginLog;
use tcCore\Student;
use tcCore\TemporaryLogin;
use tcCore\User;

class UwlrImportHelper
{
    const AUTO_UWLR_IMPORT_STATUS_PROCESSING = 'PROCESSING';
    const AUTO_UWLR_IMPORT_STATUS_DONE = 'DONE';
    const AUTO_UWLR_IMPORT_STATUS_FAILED = 'FAILED';

    public function canAddNewJobForImport() :boolean
    {
        // max 2 jobs in the queue
        $numberOfJobsInTable = DB::select("SELECT count(*) as number from jobs where payload like '%".ProcessUwlrSoapResultJob::class."%'")[0]->number;
        if($numberOfJobsInTable >= 2){
            return false;
        }
        // none in the queue after 5 o'clock and before 19 o'clock
        $now = Carbon::now();
        if($now->hour >= 5 && $now->hour <= 19){
            return false;
        }
        return true;
    }

    protected function getNextSchoolLocationForProcessing()
    {

    }
}
<?php

namespace tcCore\Http\Controllers;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use tcCore\Events\TestTakeStop;
use tcCore\Exceptions\UserFriendlyException;
use tcCore\GroupQuestionQuestion;
use tcCore\Http\Middleware\AfterResponse;
use tcCore\Http\Traits\TestTakeNavigationForController;
use tcCore\Jobs\CreatePdfFromHtmlFileAndSaveJob;
use tcCore\Question;
use tcCore\Test;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\User;
use Facades\tcCore\Http\Controllers\PdfController;

class PreviewTestTakeController extends Controller
{


    public function show(TestTake $testTake, Request $request)
    {
        //to re enable the question and groupquestion text, set this to true
        $showQuestionText = false;

        if($request->get('showQuestionText') !== null) {
            $showQuestionText = !!$request->get('showQuestionText');
        }

        $rand = Str::random(25);

        $path = sprintf('pdf/%s.pdf', $rand);
        $storagePath = storage_path($path);
        $htmlPath = sprintf('pdf/%s.html', $rand);
        $htmlStoragePath = storage_path($htmlPath);
        $doneFile = $storagePath . '.done';

        try {
            ini_set('max_execution_time', 100);
            $startTime = Carbon::now()->timestamp;

            $titleForPdfPage = $testTake->test->name.' '.Carbon::now()->format('d-m-Y H:i');
            view()->share('titleForPdfPage',$titleForPdfPage);
            view()->share('pdf_type','student_test_take');
            $testParticipants = $testTake->testParticipants()->where('test_take_status_id','>',3)->get();

            $directoryPath = dirname($htmlStoragePath);

            if (!File::exists($directoryPath)) {
                File::makeDirectory($directoryPath, 0775, true); // Maakt de directory met lees/schrijf/uitvoer rechten voor de eigenaar en alleen leesrechten voor anderen
            }
            $html = view('test-take-overview-preview',compact(['testTake','testParticipants','showQuestionText']))->render();
            file_put_contents($htmlStoragePath,$html);

            dispatch(new CreatePdfFromHtmlFileAndSaveJob($storagePath, $htmlStoragePath));

            while (!file_exists($doneFile) && (Carbon::now()->timestamp - $startTime) < 75) {
                sleep(1);
            }
            // SOLVED THROUGH LOCKFILE
            //        // the file exists the moment is starts to write, but that may not be the same time as it is closed, we've got to wait for that
            //        // as a fail safe we wait another 2 seconds;
            //        sleep(2);

            if (file_exists($storagePath)) {

                AfterResponse::$performAction[] = function () use ($storagePath, $htmlStoragePath, $doneFile) {
                    $this->deleteJobFiles($storagePath,$htmlStoragePath,$doneFile);
                };

                if(app()->runningInConsole()){
                    $this->deleteJobFiles($storagePath,$htmlStoragePath,$doneFile,true);
                }

                return response()->file($storagePath, [
                    'Content-Type'        => 'application/pdf',
                    'Content-Disposition' => 'inline; filename="toets.pdf"'
                ]);
            }
        } catch (\Throwable $e){
            $this->deleteJobFiles($storagePath,$htmlStoragePath,$doneFile);

            bugsnag::notifyException($e, function ($report) use ($testTake) {
                $report->setMetaData([
                    'code_context' => [
                        'file'      => __FILE__,
                        'class'     => __CLASS__,
                        'method'    => __METHOD__,
                        'line'      => __LINE__,
                        'timestamp' => date(DATE_ATOM),
                        'testTakeId' => $testTake->getKey()
                    ]
                ]);
            });
        }
        $request->request->set("error_id", 'PDF-'.$rand.'-'.$testTake->getKey());
        throw new UserFriendlyException(__('test-pdf.Sorry, the download could not be generated, please get in contact in order for us to help you with that.'),500);
    }

    protected function deleteJobFiles($storagePath,$htmlStoragePath,$doneFile, $keepPdfFile = false): void
    {
        if (file_exists($storagePath && !$keepPdfFile)) {
            unlink($storagePath);
        }
        if (file_exists($htmlStoragePath)) {
            unlink($htmlStoragePath);
        }
        if (file_exists($doneFile)) {
            unlink($doneFile);
        }
    }

}

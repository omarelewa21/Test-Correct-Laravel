<?php

namespace tcCore\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use tcCore\Events\TestTakeStop;
use tcCore\Exceptions\UserFriendlyException;
use tcCore\GroupQuestionQuestion;
use tcCore\Http\Middleware\AfterResponse;
use tcCore\Http\Traits\TestTakeNavigationForController;
use tcCore\Jobs\CreatePdfFromStringAndSaveJob;
use tcCore\Question;
use tcCore\Test;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\User;
use Facades\tcCore\Http\Controllers\PdfController;

class PreviewTestTakeController extends Controller
{


    public function show(TestTake $testTake, Request $request, $doDelete = true)
    {
        $titleForPdfPage = $testTake->test->name.' '.Carbon::now()->format('d-m-Y H:i');
        view()->share('titleForPdfPage',$titleForPdfPage);
        view()->share('pdf_type','student_test_take');
        $testParticipants = $testTake->testParticipants()->where('test_take_status_id','>',3)->get();
        ini_set('max_execution_time', '90');
        $html = view('test-take-overview-preview',compact(['testTake','testParticipants']))->render();

        $rand = Str::random(25);
        $path = sprintf('pdf/%s.pdf',$rand);
        $storagePath = storage_path($path);
        dispatch(new CreatePdfFromStringAndSaveJob($storagePath,$html))->onQueue('import');
        $runner = 0;
        while(!file_exists($storagePath) && $runner < 80){
            sleep(1);
            $runner++;
        }

        if(file_exists($storagePath) && $doDelete) {

            AfterResponse::$performAction[] = function () use ($storagePath) {
                if (file_exists($storagePath)) {
                    unlink($storagePath);
                }
            };

            return response()->file($storagePath, [
                'Content-Type'        => 'application/pdf',
                'Content-Disposition' => 'inline; filename="toets.pdf"'
            ]);
        }
        $request->request->set("error_id", 'PDF-'.$rand.'-'.$testTake->getKey());
        throw new UserFriendlyException(__('test-pdf.Sorry, the download could not be generated, please get in contact in order for us to help you with that.'),500);
    }



}

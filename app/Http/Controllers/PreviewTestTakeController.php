<?php

namespace tcCore\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use tcCore\GroupQuestionQuestion;
use tcCore\Http\Traits\TestTakeNavigationForController;
use tcCore\Question;
use tcCore\Test;
use tcCore\TestParticipant;
use tcCore\TestTake;
use tcCore\User;
use Facades\tcCore\Http\Controllers\PdfController;

class PreviewTestTakeController extends Controller
{

    /**
     * Creates Student Answers PDF for a TestTake
     */
    public function show(TestTake $testTake, Request $request)
    {
        $titleForPdfPage = $testTake->test->name.' '.Carbon::now()->format('d-m-Y H:i');
        view()->share('titleForPdfPage',$titleForPdfPage);
        view()->share('pdf_type','student_test_take');
        $testParticipants = $testTake->testParticipants()->where('test_take_status_id','>',3)->get();
        ini_set('max_execution_time', '90');
        $html = view('test-take-overview-preview',compact(['testTake','testParticipants']))->render();
        return response()->make(PdfController::HtmlToPdfFromString($html), 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="toets.pdf"'
        ]);
    }



}

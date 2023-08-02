<?php
namespace tcCore\Http\Controllers\TestTakes;

use Illuminate\Support\Facades\Response;
use tcCore\Attainment;
use tcCore\Http\Controllers\Controller;
use tcCore\TestTakeEvent;
use tcCore\TestTake;

class TestTakeAttainmentAnalysisController extends Controller
{

    /**
     * Display a listing of attainment scores of this test take.
     *
     * @return Response
     */
    public function index(TestTake $testTake)
    {
        return Response::make(Attainment::getAnalysisDataForTestTake($testTake));
    }


    /**
     * Display the specified test take attainment details per student.
     *
     * @param TestTakeEvent $testTakeEvent
     * @return Response
     */
    public function show(TestTake $testTake, Attainment $attainment)
    {
        return Response::make($attainment->getStudentAnalysisDataForTestTake($testTake));
    }
}

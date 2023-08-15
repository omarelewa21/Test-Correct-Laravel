<?php

namespace tcCore\Http\Controllers\TestTakes;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Controllers\Controller;
use tcCore\Jobs\SendErrorMailToSupportJob;
use tcCore\Services\RttiExportService;
use tcCore\TestTake;

class TestTakeRttiExportController extends Controller
{
    public function store(TestTake $testTake, Request $request)
    {
        $exportService = new RttiExportService($testTake);
        $rttiExportLog = $exportService->createExport();


        // if there was an error, please send an email to support
        if ($rttiExportLog->has_errors === true) {
            // mailtje sturen
            dispatch(
                new SendErrorMailToSupportJob(
                    $rttiExportLog->error,
                    __("Error bij het exporteren van RTTI"),
                    [
                        'id'           => $rttiExportLog->getKey(),
                        'test_take_id' => $rttiExportLog->test_take_id,
                        'user_id'      => $rttiExportLog->user_id,
                        'timestamp'    => $rttiExportLog->created_at->format('Y-m-d H:i:s'),
                        'reference'    => $rttiExportLog->reference
                    ]
                )
            );
            // feedback geven
            return Response::make(
                __(
                    "test-take.Er is iets fout gegaan tijdens het exporteren van de gegevens naar RTTI. Neem contact op met de support desk van Test-Correct met als referentie",
                    ['reference' => $rttiExportLog->reference]
                ),
                400
            );
        }

        $testTake->exported_to_rtti = Carbon::now();
        $testTake->save();

        // feedback van succesvolle rtti export terugsturen
        return Response::make($rttiExportLog, 200);
    }
}

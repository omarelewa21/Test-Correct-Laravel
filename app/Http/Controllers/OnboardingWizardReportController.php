<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use tcCore\OnboardingWizardReport;

class OnboardingWizardReportController extends Controller
{
    public function store()
    {
        // run realtime when not on production
        if (config('app.url_login') !== 'https://portal.test-correct.nl/') {
            \tcCore\OnboardingWizardReport::updateForAllTeachers();
        }

        $file = storage_path('onboarding_wizard_report.xls');
        if (file_exists($file)) {
            unlink($file);
        }

        Excel::create('onboarding_wizard_report', function ($excel) {

            // Set the title
            $excel->setTitle('Demo tour rapport');

            // Chain the setters
            $excel->setCreator('TLC')
                ->setCompany('TLC');


            $excel->sheet('Rapport', function ($sheet) {
                $sheet->fromArray(
                    OnboardingWizardReport::all()->toArray()
                );
            });
        })->store('xls', storage_path());

        return Response::make(['status' => 'ok'], 200);
    }

    /** todo implement file download */
    public function show()
    {
        // first generate then download;
        return Response::download(storage_path('onboarding_wizard_report.xls'));//, 'index.xls');
    }
    //
}

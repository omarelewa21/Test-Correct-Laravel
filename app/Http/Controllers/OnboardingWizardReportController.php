<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use tcCore\Exports\OnboardingWizardExport;

class OnboardingWizardReportController extends Controller
{
    protected $fileName = 'marketing_report.xls';

    public function store()
    {
        
        // run realtime when not on production
        if (config('app.url_login') !== 'https://portal.test-correct.nl/') {
            \tcCore\OnboardingWizardReport::updateForAllTeachers();
        }

        $file = storage_path($this->fileName);        
        if (file_exists($file)) {
            unlink($file);
        }

        Excel::store(new OnboardingWizardExport(),$this->fileName);

//        Excel::create('onboarding_wizard_report', function ($excel) {
//
//            // Set the title
//            $excel->setTitle('Demo tour rapport');
//
//            // Chain the setters
//            $excel->setCreator('TLC')
//                ->setCompany('TLC');
//
//
//            $excel->sheet('Rapport', function ($sheet) {
//                $sheet->fromArray(
//                    OnboardingWizardReport::all()->toArray()
//                );
//            });
//        })->store('xls', storage_path());

        return Response::make(['status' => 'ok'], 200);
    }

    /** todo implement file download */
    public function show()
    {
        
        // first generate then download;
        return Response::download(storage_path('app/'.$this->fileName));//, 'index.xls');
    }
    //
}

<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use tcCore\Exports\SchoolLocationExport;

class SchoolLocationReportController extends Controller
{
    protected $fileName = 'school_location_report.xls';

    public function store()
    {
        /** we don't do realtime reports any more as they are scheduled and becoming too big */
//        // run realtime when not on production
//        if (!in_array(config('app.url_login'),[ 'https://portal.test-correct.nl/'],true)) {
//            \tcCore\SchoolLocationReport::updateAllLocationStats();
//        }

        $file = storage_path($this->fileName);
        
        if (file_exists($file)) {
            unlink($file);
        }

        Excel::store(new SchoolLocationExport(),$this->fileName);

        return Response::make(['status' => 'ok'], 200);
    }

    public function show()
    {

        // first generate then download;
        return Response::download(storage_path('app/'.$this->fileName));
    }
    
}

<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;
use tcCore\Exports\AttainmentExport;
use tcCore\Http\Requests\AttainmentExportRequest;

class AttainmentExportController extends Controller
{
    public function export()
    {
        return Excel::download(new AttainmentExport, 'attainments_export.xlsx');
    }
}

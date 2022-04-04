<?php

namespace tcCore\Http\Controllers;


use Maatwebsite\Excel\Facades\Excel;
use tcCore\Exports\LearningGoalExport;


class LearningGoalExportController extends Controller
{
    public function export()
    {
        return Excel::download(new LearningGoalExport, 'attainments_export.xlsx');
    }
}

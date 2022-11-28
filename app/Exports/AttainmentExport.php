<?php

namespace tcCore\Exports;


use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use tcCore\BaseSubject;

class AttainmentExport implements WithMultipleSheets
{



    /**
     * @return array
     */
    public function sheets(): array
    {
        $sheets = [];
        $baseSubjects = BaseSubject::all();
        foreach ($baseSubjects as $baseSubject) {
            $sheets[] = new AttainmentExportSheet($baseSubject);
        }
        return $sheets;
    }
}

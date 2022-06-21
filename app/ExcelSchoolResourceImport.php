<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 27/10/2020
 * Time: 14:27
 */

namespace tcCore;


use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ExcelSchoolResourceImport implements WithHeadingRow
{
    use Importable;

    public function headingRow(): int
    {
        return 1;
    }
}
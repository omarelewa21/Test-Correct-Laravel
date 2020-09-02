<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 11/08/2020
 * Time: 13:06
 */

namespace tcCore\QtiModels;


use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Cell;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;

class ExcelManifest
{
    public $data;

    public function __construct($excelFile)
    {
        $this->data = Excel::toArray(new ResourceImport, $excelFile)[0];
    }

    public function getTestWithResourceList()
    {
        $result = [];

        foreach ($this->data as $row) {
            $levels = $this->transformArrayStructureToArray($row['levels']);
            $items = $this->transformArrayStructureToArray($row['items']);

            $testName = sprintf(
                '%s | %s',
                $row['learning_objective'],
                trim(substr($row['domain'], strpos($row['domain'], ' - ' )+3))
            );

            foreach ($levels as $level) {
                $result[] = [
                    'level' => $level,
                    'test' => $testName,
                    'items' => $items->toArray(),
                ];
            }
        }
        return $result;
    }

    private function transformArrayStructureToArray($structs) {
        return collect(explode(',', $structs))
            ->map(function ($struct) {
                return trim(str_replace(['[', ']', '\''], ['', '', ''], $struct));
            });
    }
}

class ResourceImport implements WithHeadingRow
{
    use Importable;

    public function headingRow(): int
    {
        return 1;
    }
}



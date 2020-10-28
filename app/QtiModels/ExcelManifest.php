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

    public function getTestListWithResources()
    {
        $result = [];

        foreach ($this->data as $row) {
            $levels = $this->transformArrayStructureToArray($row['levels']);
            $items = $this->transformArrayStructureToArray($row['items']);

            $testName = sprintf(
                '%s | %s | %s',
                $row['learning_objective'],
                trim(substr($row['domain'], strpos($row['domain'], ' - ') + 3)),
                self::orderByHighestEducationLevel($levels)->implode(', ')
            );

            $result[] = [
                'highest_level' => $this->getHighestEducationLevel($levels),
                'levels' => $levels->toArray(),
                'test' => $testName,
                'items' => $items->toArray(),
            ];
        }
        return $result;
    }

    private function transformArrayStructureToArray($structs)
    {
        return collect(explode(',', $structs))
            ->map(function ($struct) {
                return trim(str_replace(['[', ']', '\''], ['', '', ''], $struct));
            });
    }

    public static function getHighestEducationLevel($levels) {
       return self::orderByHighestEducationLevel($levels)->first();
    }

    public static function orderByHighestEducationLevel($levels) {
        $arr = [
            'vwo' =>  60,
            'havo' => 50,
            'gl/tl' => 40,
            'kb' => 30,
        ];
        return $levels->sort(function($a,$b) use ($arr) {
            return $arr[$a] < $arr[$b];
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



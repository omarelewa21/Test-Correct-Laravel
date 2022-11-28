<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 11/08/2020
 * Time: 13:06
 */

namespace tcCore;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Cell;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;

class ExcelAttainmentUpdateOrCreateManifest extends ExcelAttainmentManifest
{
    public function __construct($excelFile)
    {
        $this->data = Excel::toArray(new ExcelAttainmentResourceImport, $excelFile);
    }

    public function getData()
    {
        return $this->data;
    }

    public function getAttainmentResources()
    {
        $result = [];

        foreach ($this->data as $sheet) {
            foreach ($sheet as $row) {
                if (!array_key_exists('id', $row)) {
                    continue;
                }
                if (!array_key_exists('status', $row) || empty($row['base_subject_id'])) {
                    continue;
                }
                $result[] = (object)array_merge($row, [
                    'id' => $row['id'],
                    'base_subject_id' => $row['base_subject_id'],
                    'base_subject_name' => $row['base_subject_name'],
                    'education_level_name' => $row['education_level_name'],
                    'education_level_id' => $row['education_level_id'],
                    'attainment_id' => (trim($row['attainment_id'])=='NULL'||is_null($row['attainment_id']))?null:$row['attainment_id'],
                    'code' => $row['code'],
                    'subcode' => $row['subcode'],
                    'subsubcode' => $row['subsubcode'],
                    'description' => $row['description'],
                    'status' => $row['status'],
                    'temp_id' => Arr::get($row, 'temp_id', null),
                    'parent_temp_id' => Arr::get($row, 'parent_temp_id', null),
                ]);
            }
        }
        return $result;
    }

}
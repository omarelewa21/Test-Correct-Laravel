<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 11/08/2020
 * Time: 13:06
 */

namespace tcCore;


use Illuminate\Database\Eloquent\Model;
use Maatwebsite\Excel\Cell;
use Maatwebsite\Excel\Concerns\Importable;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Facades\Excel;

class ExcelAttainmentManifest
{
    public $data;
    protected $subjectReference = [];

    public function __construct($excelFile)
    {
        $this->data = Excel::toArray(new ExcelAttainmentResourceImport, $excelFile)[0];
    }

    public function getAttainmentResources()
    {
        $result = [];

        foreach ($this->data as $row) {

            $result[] = (object) array_merge($row,[
                'base_subject_id' => $this->getBaseSubjectId($row['vak']),
                'education_level_id' => $this->getEducationLevelIdFromLevel($row['niveau']),
                'code' => $row['hoofdcode'],
                'subcode' => $row['subcode'],
                'description' => $row['beschrijving'],
            ]);
        }
        return $result;
    }

    protected function getEducationLevelIdFromLevel($level)
    {
        $level = trim($level);
        $ar = [
            'vwo' => 1,
            'havo' => 3,
            'kb' => 6,
            'gl/tl' => 4,
            'havo-vwo' => [1,3],
            'vmbo' => 4,
        ];
        if (!array_key_exists($level, $ar)) {
            throw new \Exception(sprintf('Expected level %s unknown in class %s', $level, __CLASS__));
        }
        return $ar[$level];
    }

    protected function getBaseSubjectId($vak)
    {
        if(!array_key_exists($vak,$this->subjectReference)){
            $subject = BaseSubject::where('name',$vak)->first();
            if(!$subject){
                throw new \Exception(sprintf('Subject (%s) unknown in class %s',$vak, __CLASS__));
            }
            $this->subjectReference[$vak] = $subject->getKey();
        }

        return $this->subjectReference[$vak];
    }

}

class ExcelAttainmentResourceImport implements WithHeadingRow
{
    use Importable;

    public function headingRow(): int
    {
        return 1;
    }
}



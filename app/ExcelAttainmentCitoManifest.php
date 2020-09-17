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

class ExcelAttainmentCitoManifest
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

            $codeSubcodeAr = $this->getCodeSubcodes($row['learning_objective'],$row['domain']);
            $levelIds = $this->getEducationLevelIdsFromLevel($row['levels']);
            $result[] = (object) array_merge($row,[
                'external_id' => $row['item_code'],
                'highest_level' => $this->getHighestEducationLevelId($levelIds),
                'levels' => $levelIds,
                'code_subcode_ar' => $codeSubcodeAr,
                'domain' => $row['domain'],
                'learning_objective' => $row['learning_objective']
            ]);

            if( isset($row['levels-1']) && $row['levels-1'] &&
                isset($row['learning_objective-1']) && $row['learning_objective-1'] &&
                isset($row['domain-1']) && $row['domain-1']){
                $levelIds = $this->getEducationLevelIdsFromLevel($row['levels-1']);
                $result[] = (object) array_merge($row,[
                    'external_id' => $row['item_code'],
                    'highest_level' => $this->getHighestEducationLevelId($levelIds),
                    'levels' => $levelIds,
                    'code_subcode_ar' => $codeSubcodeAr,
                    'domain' => $row['domain-1'],
                    'learning_objective' => $row['learning_objective-1']
                ]);
            }

        }
        return $result;
    }

    protected function getCodeSubcodes($learningObjective, $domain)
    {
        $domain = trim(explode('-',$domain)[0]);

        $learningObjective = trim(explode('-',$learningObjective)[0]);
        return collect(explode(',',$learningObjective))
            ->map(function($a){
                return trim($a);
            })
            ->map(function($objective) use ($domain){
                $letter = $domain{0};
                $nrRaw = substr($domain,1);
                $nr = (int) $nrRaw;
//        $code = sprintf('%s%s',$letter,$nr);
                $short = sprintf('%s%s',$letter,$nr);;
                $code = $domain;
                $subcode = str_replace([$domain,$code, $short, $letter],'',$objective);
                if(substr($subcode,0,strlen($nr)) == $nr){
                    $subcode = substr($subcode,strlen($nr));
                }
                return ['code' => $code, 'subcode' => $subcode];
            })
            ->toArray();
    }

    protected function getHighestEducationLevelId($levels) {
        return $this->orderByHighestEducationLevelId($levels)->first();
    }

    protected function orderByHighestEducationLevelId($levels) {
        $arr = [
            1 =>  60,
            3 => 50,
            4 => 40,
            6 => 30,
        ];
        return collect($levels)->sort(function($a,$b) use ($arr) {
            return $arr[$a] < $arr[$b];
        });
    }

    protected function transformArrayStructureToArray($structs)
    {
        return collect(explode(',', $structs))
            ->map(function ($struct) {
                return trim(str_replace(['[', ']', '\''], ['', '', ''], $struct));
            })->toArray();
    }

    protected function getEducationLevelIdsFromLevel($level)
    {
        $levels = [];
        $ar = [
            'vwo' => 1,
            'havo' => 3,
            'kb' => 6,
            'gl/tl' => 4,
        ];
        foreach($this->transformArrayStructureToArray($level) as $level) {
            $level = trim($level);
            if (!array_key_exists($level, $ar)) {
                throw new \Exception(sprintf('Expected level %s unknown in class %s', $level, __CLASS__));
            }
            $levels[] = $ar[$level];
        }
        return $levels;
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



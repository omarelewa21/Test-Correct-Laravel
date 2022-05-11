<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 11/08/2020
 * Time: 13:06
 */

namespace tcCore;


use Maatwebsite\Excel\Facades\Excel;


class ExcelSchoolImportManifest
{
    public $data;

    public function __construct($excelFile)
    {
        $this->data = Excel::toArray(new ExcelAttainmentResourceImport(), $excelFile)[0];
        $this->failOnFirstMissingBaseSubject = $failOnFirstMissingBaseSubject;
    }

    public function getSectionResources()
    {
        $result = [];

        foreach($this->data as $row){
            if($row['sectie']) {
                $result[] = $row['sectie'];
            }
        }

        return collect($result);
    }

    public function getSubjectResources()
    {
        $result = [];

        foreach ($this->data as $row) {
            if($row['categorie']) {
                $result[] = (object)array_merge($row, [
                    'base_subject_id' => $this->getBaseSubjectId($row['categorie']),
                    'default_section_id' => $this->getDefaultSectionId($row['sectie']),
                    'education_levels' => $row['niveau'],
                    'abbreviation' => $row['afkorting'],
                    'name' => $row['vaknaam_test_correct'],
                ]);
            }
        }

        if($this->hasErrors){
            exit;
        }
        return collect($result);
    }

    protected function getDefaultSectionId($section)
    {
        if(!$this->sections){
            $this->sections = DefaultSection::pluck('id','name');
        }

        $id = $this->sections->first(function($value, $key) use ($section) {
            return $key == $section;
        });

        if(!$id){
            throw new \Exception(sprintf('Default section %s non existent',$section));
        }

        return $id;
    }

    protected function getBaseSubjectId($subject)
    {
        if(!$this->baseSubjects){
            $this->baseSubjects = BaseSubject::pluck('id','name');
        }

        $id = $this->baseSubjects->first(function($value, $key) use ($subject) {
           return $key == $subject;
        });

        if(!$id){
            $this->hasErrors = true;
            if($this->failOnFirstMissingBaseSubject) {
                throw new \Exception(sprintf('Base subject %s non existent', $subject));
            } else {
                logger('missing base subject '.$subject);
            }
        }

        return $id;
    }

}





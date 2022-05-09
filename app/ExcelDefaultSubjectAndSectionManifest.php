<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 11/08/2020
 * Time: 13:06
 */

namespace tcCore;


use Maatwebsite\Excel\Facades\Excel;


class ExcelDefaultSubjectAndSectionManifest
{
    public $data;
    protected $sections;
    protected $baseSubjects;

    public function __construct($excelFile)
    {
        $this->data = Excel::toArray(new ExcelDefaultSubjectAndSectionResourceImport(), $excelFile)[0];
    }

    public function getSectionResources()
    {
        $result = [];
        foreach($this->data as $row){
            $result[] = $row['Sectie'];
        }

        return collect($result);
    }

    public function getSubjectResources()
    {
        $result = [];

        foreach ($this->data as $row) {

            $result[] = (object) array_merge($row,[
                'base_subject_id' => $this->getBaseSubjectId($row['Categorie']),
                'default_section_id' => $this->getDefaultSectionId($row['Sectie']),
                'education_levels' => $this->getEducationLevels($row['Niveau']),
                'abbreviation' => $row['Afkorting'],
                'name' => $row['Vaknaam Test-Correct'],
            ]);
        }
        return collect($result);
    }

    protected function getEducationLevels($string)
    {
        return collect(explode(';',$string));
    }

    protected function getDefaultSectionId($section)
    {
        if(!$this->sections){
            $this->sections = DefaultSection::pluck('id','name');
        }

        $id = $this->sections->first(function($value, $key) use ($section) {
            return $key == $section;
        })->map(function($value,$key){
            return $value;
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
        })->map(function($value,$key){
            return $value;
        });

        if(!$id){
            throw new \Exception(sprintf('Base subject %s non existent',$subject));
        }

        return $id;
    }

}





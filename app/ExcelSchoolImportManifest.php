<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 11/08/2020
 * Time: 13:06
 */

namespace tcCore;


use Illuminate\Support\Str;
use Maatwebsite\Excel\Facades\Excel;


class ExcelSchoolImportManifest
{
    public $data;
    protected $schoolLocationTransformer = [
        'BRIN NUMMER' => 'external_main_code',
        'Locatie brin code 2 karakters max' => 'external_sub_code',
        'naam' => 'name',
        'Klantcode' => 'customer_code',
        'Vestiging adres' => 'main_address',
        'Vestiging postcode' => 'main_postal',
        'Vestiging stad' => 'main_city',
        'Vestiging Land' => 'main_country',
        'TELEFOONNUMMER' => 'main_phonenumber',
        'INTERNETADRES' => 'internetaddress',
        'ONDERWIJSSTRUCTUUR' => 'ONDERWIJSSTRUCTUUR',
        'Factuuradres adres' => 'invoice_address',
        'Factuuradres Postcode' => 'invoice_postal',
        'Factuuradres Stad' => 'invoice_city',
        'Factuuradres Land' => 'invoice_country',
        'Bezoekadres adres' => 'visit_address',
        'Bezoekadres Postcode' => 'visit_postal',
        'Bezoekadres Stad' => 'visit_city',
        'Bezoekadres Land' => 'visit_country',
        'Hubspot ID' => 'company_id',
    ];

    protected $schoolTransformer = [
        'Naam scholengemeenschap' => 'name',
        'BRIN4' => 'external_main_code',
        'Klantcode' => 'customer_code',
        'Vestigingsadres adres' => 'main_address',
        'Vestigingsadres postcode' => 'main_postal',
        'Vestigingsadres stad' => 'main_city',
        'Vestigingsadres land' => 'main_country',
        'factuuradres adres' => 'invoice_address',
        'factuuradres postcode' => 'invoice_postal',
        'factuuradres stad' => 'invoice_city',
        'factuuradres land' => 'invoice_country',
    ];

    public function __construct($excelFile)
    {
        $this->data = Excel::toArray(new ExcelSchoolResourceImport(), $excelFile);
    }

    public function getSchoolLocations()
    {
        $result = [];

        foreach($this->data[0] as $row){
            if($row['brin_nummer']) {
                $result[] = array_merge($row,
                    $this->getTransformedAndCheckedData($row, $this->schoolLocationTransformer, 'school location (tab 1)',true)
                );
            }
        }

        return collect($result);
    }

    protected function getTransformedAndCheckedData($row, $transformer, $type, $isSchoolLocatie = false)
    {

        $return = [];
        foreach($transformer as $key => $value){
            $transformedKey = Str::slug($key,'_');
            if(!array_key_exists($transformedKey,$row)){
                throw new \Exception(sprintf('Not all data is available, column `%s` is missing for the %s (%s => %s)',$key, $type, $transformedKey, var_export($row,true)));
            }
            $return[$value] = $row[$transformedKey];

            if(!$return[$value] && $value !== 'customer_code'){
                $return[$value] = '-';
            }
        }

        if($isSchoolLocatie){
            $return['external_main_code'] = substr($row['vestigingsnummer'],0,4);
            $return['external_sub_code'] = substr($row['vestigingsnummer'],4,2);
            $return['school_id'] = School::where('external_main_code',$row['brin_nummer'])->value('id');

            if($return['external_main_code'].$return['external_sub_code'] != $row['vestigingsnummer'] || $row['locatie_brin_code_2_karakters_max'] != $return['external_sub_code']) {
                throw new \Exception(sprintf('brin nummer (%s) icm locatie code (%s) komen niet overeen met de vestigingscode (%s) ##%s **%s', $return['external_main_code'], $return['external_sub_code'], $row['vestigingsnummer'], var_export($return, true), var_export($row, true)));
            }
        }
        return $return;
    }

    public function getSchools()
    {
        $result = [];

        foreach($this->data[1] as $row){
            if($row['naam_scholengemeenschap']) {
                $result[] = $this->getTransformedAndCheckedData($row, $this->schoolTransformer, 'school (tab 2)');
            }
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





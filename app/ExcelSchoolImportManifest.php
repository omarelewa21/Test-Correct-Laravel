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
use tcCore\Exceptions\SchoolAndSchoolLocationsImportException;


class ExcelSchoolImportManifest
{
    public $data;
    protected $schoolLocationTransformer = [
        'BRIN NUMMER' => 'brin_school_external_code',
        'Locatie brin code 2 karakters max' => 'brin_schoollocation_subcode',
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
        'Accountmanager' => 'user_id',
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

    protected $transformedSchoolLocations;
    protected $transformedSchools;

    protected $doValidationCheck = true;

    protected $errors = [];

    protected CONST FATAL_ERROR = "FATAL ERROR:";

    public function __construct($excelFile, $importer = null, $doValidationCheck = true)
    {
        $this->data = Excel::toArray(new ExcelSchoolResourceImport(), $excelFile);
        $this->doValidationCheck = $doValidationCheck;
        $this->checkSchoolsIntegrity($importer);
        $this->checkSchoolLocationsIntegrity($importer);
        if($this->hasErrors() && $doValidationCheck){
            throw new SchoolAndSchoolLocationsImportException(json_encode($this->getErrors()));
        }
    }

    protected function addError($error)
    {
        $this->errors[] = $error;
    }

    public function getErrors()
    {
        return $this->errors;
    }

    public function hasErrors()
    {
        return !! count($this->errors);
    }

    protected function checkSchoolsIntegrity($importer = null)
    {
        // check for no double customer codes
        // check for no double external_main_codes
        $inDB = School::select('customer_code','name','external_main_code')->get();
        $externalMainCodesInImport = [];
        $customerCodesInImport = [];
        if($importer && method_exists($importer,'inform')){
            $importer->inform('going to check the integrity of the schools');
        }
        $this->getTransformedSchools()->each(function($row) use($inDB, &$externalMainCodesInImport, &$customerCodesInImport, $importer){
            $fromDBFound = false;
            if($importer && method_exists($importer,'inform')){
                $importer->inform('checking school '.$row['name']);
            }
            // is the customer code set
            if($row['customer_code']){
                // do we have one with the same customer code in the import
                if(in_array($row['customer_code'],$customerCodesInImport)){
                    $this->addError(sprintf('%s We have two schools in the import with the same customer code %s',self::FATAL_ERROR,$row['customer_code']));
                }

                if($this->doValidationCheck) {
                    // do we have one with the same customer code in the database
                    $fromDB = $inDB->first(function($el) use ($row){
                        return Str::lower($el->customer_code) === Str::lower($row['customer_code']);
                    });
                    if ($fromDB) {
                        // if so do they have different external main codes
                        $fromDBByExternalCode = $inDB->first(function($el) use ($row) {
                            return Str::lower($el->external_main_code) === Str::lower($row['external_main_code']) && $el->external_main_code != '' && null != $el->external_main_code;
                        });
                        if (!$fromDBByExternalCode && ($fromDB->external_main_code != $row['external_main_code'] && $fromDB->external_main_code != '' && null !== $fromDB->external_main_code)) {
                            $this->addError(sprintf('We have different school brin fours (DB:%s => import:%s) for the same customer code %s %s', $fromDB->external_main_code, $row['external_main_code'], $fromDB->customer_code, $row['customer_code']));
                        }
                        // or different names
                        if ($fromDB->name != $row['name']) {
                            $this->addError(sprintf('We have different school names (db:%s => import:%s) with the customer code %s', $fromDB->name, $row['name'], $row['customer_code']));
                        }
                        $fromDBFound = true;
                    }
                }
                if(!$fromDBFound){
                    $fromDBByExternalCode = $inDB->first(function($el) use ($row) {
                        return Str::lower($el->external_main_code) === Str::lower($row['external_main_code']) && $el->external_main_code != '' && null != $el->external_main_code;
                    });
                    if($fromDBByExternalCode){
                        $this->addError(sprintf('%s We have a school with same external main code (%s) but different customer code (DB:%s => import: %s)',self::FATAL_ERROR, $row['external_main_code'], $fromDBByExternalCode->customer_code, $row['customer_code']));
                    }
                }
            } else {
                // no external main code available
                $this->addError(sprintf('%s Missing customer code for %s',self::FATAL_ERROR, $row['name']));
            }
            // is the external main code set?
            if($row['external_main_code'] && $row['external_main_code'] !== '-'){
                // do we have one with the same extneral main code in the import
                if(in_array($row['external_main_code'],$externalMainCodesInImport)){
                    $this->addError(sprintf('%s We have two schools in the import with the same BRIN four %s',self::FATAL_ERROR, $row['external_main_code']));
                }

                if($this->doValidationCheck) {
                    // do we have one in the database with the same external_main_code
                    $fromDB = $inDB->first(function($el) use ($row) {
                        return Str::lower($el->external_main_code) === Str::lower($row['external_main_code']);
                    });
                    if ($fromDB) {
                        // if so do they have different customer codes
                        if (Str::lower($fromDB->customer_code) !== Str::lower($row['customer_code'])) {
                            $this->addError(sprintf('We have different school customer_codes (db:%s => import:%s) with the BRIN four %s', $fromDB->customer_code, $row['customer_code'], $row['external_main_code']));
                        }
                        // or different names
                        if ($fromDB->name != $row['name']) {
                            $this->addError(sprintf('We have different school names (db:%s => import:%s) with the BRIN four %s', $fromDB->name, $row['name'], $row['external_main_code']));
                        }

//                        $this->transformedSchools = $this->transformedSchools->reject(function ($row) use ($fromDB) {
//                            return Str::lower($row['external_main_code']) === Str::lower($fromDB->external_main_code);
//                        });
                    }
                }
            } else {
                // no external main code available
                $this->addError(sprintf('%s Missing brin four for %s',self::FATAL_ERROR, $row['name']));
            }
            $customerCodesInImport[] = $row['customer_code'];
            $externalMainCodesInImport[] = $row['external_main_code'];
        });
    }

    protected function checkSchoolLocationsIntegrity($importer = null)
    {
        // check for no double customer codes
        // check for no double external_main_codes
        $inDB = SchoolLocation::select('customer_code','name','external_main_code','external_sub_code')->get();
        $externalMainCodesInImport = [];
        $externalBRINInImport = [];
        $customerCodesInImport = [];
        if($importer && method_exists($importer,'inform')){
            $importer->inform('going to check the integrity of the school locations');
        }
        $this->getTransformedSchoolLocations()->each(function($row, $key) use($inDB, &$externalMainCodesInImport,&$externalBRINInImport, &$customerCodesInImport, $importer){
            $importer->inform('checking integrity of '.$row['name']);
            $fromDBFound = false;
            if($importer && method_exists($importer,'inform')){
                $importer->inform('checking location '.$row['name']);
            }
            // reset data
            $fromDB = null;
            if($row['customer_code']){
                if(in_array($row['customer_code'],$customerCodesInImport)){
                    $this->addError(sprintf('%s We have two school locations in the import with the same customer code %s',self::FATAL_ERROR, $row['customer_code']));
                }

                if($this->doValidationCheck) {
                    $fromDB = $inDB->first(function($el) use ($row) {
                        return Str::lower($el->customer_code) === Str::lower($row['customer_code']);
                    });
                    if ($fromDB) {
                        $fromDBByExternalCode = $inDB->first(function($el) use ($row) {
                            return Str::lower($el->external_main_code) === Str::lower($row['external_main_code']) && Str::lower($el->external_sub_code) === Str::lower($row['external_sub_code']);
                        });
                        if (!$fromDBByExternalCode && (Str::lower($fromDB->external_main_code) !== Str::lower($row['external_main_code']) || Str::lower($fromDB->external_sub_code) !== Str::lower($row['external_sub_code']))) {
                            $this->addError(sprintf('We have different school location brins (DB:%s/%s => import:%s/%s) for the same customer code %s', $fromDB->external_main_code, $fromDB->external_sub_code, $row['external_main_code'], $row['external_sub_code'], $fromDB->customer_code));
                        }
                        // or different names
                        if ($fromDB->name != $row['name']) {
                            $this->addError(sprintf('We have different school location names (db:%s => import:%s) with the customer code %s', $fromDB->name, $row['name'], $row['customer_code']));
                        }
                        $fromDBFound = true;
                    }
                }
            }
            else {
                // no external main code available
                $this->addError(sprintf('%s Missing customer code for %s',self::FATAL_ERROR, $row['name']));
            }
            if(!$fromDBFound){
                $fromDBByExternalCode = $inDB->first(function($el) use ($row) {
                    return Str::lower($el->external_main_code) === Str::lower($row['external_main_code']) && Str::lower($el->external_sub_code) === Str::lower($row['external_sub_code']);
                });
                if($fromDBByExternalCode){
                    $this->addError(sprintf('%s We have a schoollocation with same external code (%s/%s) but different customer code (DB:%s => import: %s)',self::FATAL_ERROR, $row['external_main_code'], $row['external_sub_code'], $fromDBByExternalCode->customer_code, $row['customer_code']));
                }
            }
            // check if external_main_code and external_sub_code are available
            if($row['external_main_code'] && $row['external_sub_code']){
                // is there another one in the list with the same credentials
                if(in_array(sprintf('%s%s',$row['external_main_code'],$row['external_sub_code']),$externalBRINInImport)){
                    $this->addError(sprintf('%s We have school locations in the import with the same BRIN %s-%s',self::FATAL_ERROR, $row['external_main_code'],$row['external_sub_code']));
                }

                if($this->doValidationCheck) {
                    // do we have one in the database
                    $fromDB = $inDB->first(function($el) use ($row) {
                        return Str::lower($el->external_main_code) === Str::lower($row['external_main_code']) && Str::lower($el->external_sub_code) === Str::lower($row['external_sub_code']);
                    });
                    if ($fromDB) {
                        // if so, do they have different customer codes
                        if ($fromDB->customer_code != $row['customer_code']) {
                            $this->addError(sprintf('We have different school location customer_codes (db:%s => import:%s) with the BRIN %s/%s', $fromDB->customer_code, $row['customer_code'], $row['external_main_code'], $row['external_sub_code']));
                        }
                        // or different names
                        if ($fromDB->name != $row['name']) {
                            $this->addError(sprintf('We have different school location names (db:%s => import:%s) with the BRIN %s/%s', $fromDB->name, $row['name'], $row['external_main_code'], $row['external_sub_code']));
                        }

//                        $this->transformedSchoolLocations = $this->transformedSchoolLocations->reject(function ($row) use ($fromDB) {
//                            return Str::lower($row['external_main_code']) === Str::lower($fromDB->external_main_code) && Str::lower($row['external_sub_code']) === Str::lower($fromDB->external_sub_code);
//                        });
                    }
                }
            } else {
                // no brin data available
                $this->addError(sprintf('%s Missing brin for %s',self::FATAL_ERROR, $row['name']));
            }
            $customerCodesInImport[] = $row['customer_code'];
            $externalMainCodesInImport[] = $row['external_main_code'];
            $externalBRINInImport[] = sprintf('%s%s',$row['external_main_code'],$row['external_sub_code']);
        });
    }

    public function getTransformedSchoolLocations()
    {
        if(null === $this->transformedSchoolLocations){
            $this->transformedSchoolLocations = $this->getSchoolLocations();
        }
        return $this->transformedSchoolLocations;
    }

    protected function getSchoolLocations()
    {
        $result = [];

        foreach($this->data[0] as $row){
            if($row['naam']) {
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
                throw new SchoolAndSchoolLocationsImportException(sprintf('Not all data is available, column `%s` is missing for the %s (%s => %s)',$key, $type, $transformedKey, var_export($row,true)));
            }
            $return[$value] = $row[$transformedKey];

            if(!$return[$value] && $value !== 'customer_code'){
                $return[$value] = '-';
            }
        }

        if($isSchoolLocatie){
            $return['external_main_code'] = substr($row['vestigingsnummer'],0,4);
            $return['external_sub_code'] = substr($row['vestigingsnummer'],4,2);
//            $return['school_id'] = School::where('external_main_code',$row['brin_nummer'])->value('id'); // can't be done, may not be added yet

//            if($return['external_main_code'].$return['external_sub_code'] != $row['vestigingsnummer'] || $row['locatie_brin_code_2_karakters_max'] != $return['external_sub_code']) {
//                throw new SchoolAndSchoolLocationsImportException(sprintf('brin nummer (%s) icm locatie code (%s) komen niet overeen met de vestigingscode (%s) ##%s **%s', $return['external_main_code'], $row['locatie_brin_code_2_karakters_max'], $row['vestigingsnummer'], var_export($return, true), var_export($row, true)));
//            }
        }
        return $return;
    }


    public function getTransformedSchools()
    {
        if(null === $this->transformedSchools){
            $this->transformedSchools = $this->getSchools();
        }
        return $this->transformedSchools;
    }

    protected function getSchools()
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
            throw new SchoolAndSchoolLocationsImportException(sprintf('Default section %s non existent',$section));
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
                throw new SchoolAndSchoolLocationsImportException(sprintf('Base subject %s non existent', $subject));
            } else {
                logger('missing base subject '.$subject);
            }
        }

        return $id;
    }

}





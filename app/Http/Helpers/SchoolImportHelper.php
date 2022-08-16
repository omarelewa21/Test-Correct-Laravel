<?php

namespace tcCore\Http\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use tcCore\DefaultSection;
use tcCore\DefaultSubject;
use tcCore\EducationLevel;
use tcCore\ExcelSchoolImportManifest;
use tcCore\Exceptions\SchoolAndSchoolLocationsImportException;
use tcCore\Jobs\CreateSchoolLocationFromImport;
use tcCore\Jobs\UpdateSchoolLocationFromImport;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\UmbrellaOrganization;
use tcCore\User;
use Throwable;

class SchoolImportHelper
{

    protected $umbrellaOrganzationId;
    protected $schoolId;
    protected $schoolLocationId;

    protected $educationLevels;

    protected $inConsole = false;
    protected $path;
    protected $manifest;
    protected $user;

    protected $echoDetails = false;
    protected $error;

    public function __construct($echoDetails = false)
    {
        set_time_limit(300);
        $this->echoDetails = $echoDetails;
    }

    public function setFilePath($path)
    {
        $this->path = $path;
        return $this;
    }


    public function handleImport($doValidationCheck = true)
    {

        DB::beginTransaction();
        try {
            if(!$this->path || !file_exists($this->path)){
                return;
            }
            // check for available defauls sections and subjects
            $this->inform('going to check for available (and required) default sections and subjects');
            $this->checkForRequiredDefaultSectionsAndSubjects();
            GlobalStateHelper::getInstance()->setQueueAllowed(false);
            GlobalStateHelper::getInstance()->setPreventDemoEnvironmentCreationForSchoolLocation(true);
            $this->inform('Going to start with the file prep and validation of the data');
            $this->prepareDataFromFile($doValidationCheck);
            $this->inform('done with the file preparation, up to set the user');
            $this->prepareUser();
            $this->inform('done with the user, next handle schools');
            $this->handleSchools();
            $this->inform('done with the schools, next are the school locations');
            $this->handleSchoolLocations();
            $this->inform('all done');
            GlobalStateHelper::getInstance()->setPreventDemoEnvironmentCreationForSchoolLocation(false);
            GlobalStateHelper::getInstance()->setQueueAllowed(true);
            DB::commit();
            $this->inform('all done');
        } catch(Throwable $e) {
            DB::rollback();
            logger($e);
            if($this->echoDetails){
                dd($e->getMessage());
            }
            throw $e;
        }
    }

    public function checkForExistensInDatabaseAndThrowExceptionIfTheCase($row)
    {
        if(($row['customer_code'] && SchoolLocation::where('customer_code',$row['customer_code'])->exists()) || SchoolLocation::where('external_main_code',$row['external_main_code'])->where('external_sub_code',$row['external_sub_code'])->exists()){
            throw new SchoolAndSchoolLocationsImportException(sprintf('School location with name %s already in the system based on (customer_code: %s, external_main_code:%s, external_sub_code:%s',$row['name'],$row['customer_code'],$row['external_main_code'],$row['external_sub_code']));
        }
    }

    public function hasError()
    {
        return !! $this->error;
    }

    public function getError()
    {
        return $this->error;
    }

    public function inform($info)
    {
        if($this->echoDetails){
            echo ($info.PHP_EOL);
        }
    }

    protected function prepareUser()
    {
        $this->user = User::where('username','info+ab@test-correct.nl')->orWhere('username','carloschoep+accountmanager@hotmail.com')->orWhere('username','info+testportalaccountmanager@test-correct.nl')->orWhere('username','c@teachandlearncompany.com')->orderBy('created_at','asc')->first();
        if(!$this->user){
            throw new SchoolAndSchoolLocationsImportException('Could not find a valid account manager, I`ve searched for info+ab@test-correct.nl, carloschoep+accountmanager@hotmail.com, info+testportalaccountmanager@test-correct.nl and c@teachandlearncompany.com');
        }
    }

    protected function checkForRequiredDefaultSectionsAndSubjects()
    {
        if(!DefaultSection::exists() || !DefaultSubject::exists()){
            throw new SchoolAndSchoolLocationsImportException('Default sections and subjects are required, did you forget to import them?');
        }
    }

    protected function handleSchools()
    {
        $data = $this->manifest->getTransformedSchools();
        $data->each(function($row){
           if($school = School::where('customer_code',$row['customer_code'])->first()){
               if($row['name'] !== '-') {
                   $this->updateSchoolFromImport($school, (array)$row, $this->user);
               }
           } else {
               if($row['name'] !== '-') {
                   $this->createSchool((array)$row, $this->user);
               }
           }
        });
    }

    protected function  handleSchoolLocations()
    {
        $data = $this->manifest->getTransformedSchoolLocations();
        $data->each(function($row){
            if($row['name'] !== '-') {
                if(!SchoolLocation::where('customer_code',$row['customer_code'])->exists()) {
                    $this->inform('dispatch create school location '.$row['name'].' => memory '.$this->getMemorySize());
                    CreateSchoolLocationFromImport::dispatch($row,$this->user);
//                    $location = $this->createSchoolLocation($row, $this->user);
                } else {
                    $this->inform('dispatch update school location '.$row['name'].' => memory '.$this->getMemorySize());
                    UpdateSchoolLocationFromImport::dispatch($row,$this->user);
                }
            }
        });
    }

    protected function prepareDataFromFile($doValidationCheck = true)
    {
        $this->manifest = new ExcelSchoolImportManifest($this->path,$this, $doValidationCheck);
    }

    protected function cleanUmbrellaOrganization()
    {
        $this->umbrellaOrganzationId = null;
        $this->cleanSchool();
        return $this;
    }

    protected function cleanSchool()
    {
        $this->schoolId = null;
        return $this;
    }

    public function createUmbrellaOrganization($data, $user = null)
    {
        $this->schoolId = null;
        $this->schoolLocationId = null;
        $data = $this->transformForUmbrellaOrganization($data);
        return $this->createProperty(new UmbrellaOrganization(),$data,$user);
    }

    public function createSchool($data, $user = null)
    {
        $this->schoolLocationId = null;
        if($this->umbrellaOrganzationId){
            $data['umbrella_organization_id'] = $this->umbrellaOrganzationId;
        }
        return $this->createProperty(new School(),$data,$user);
    }

    protected function updateSchoolFromImport(School $school, $data, $user = null)
    {
        $this->schoolLocationId = null;
        foreach($data as $key => $value){
            if($value == '-'){
                if($school->$key != ''){
                   continue;
                }
            }
            if($school->hasAttribute($key)) {
                $school->$key = $value;
            }
        }
        $school->save();
    }

    public function updateSchoolLocationFromImport(SchoolLocation $schoolLocation, $data)
    {
        foreach($data as $key => $value){
            if($value == '-'){
                if($schoolLocation->$key != ''){
                    continue;
                }
            }
            if($schoolLocation->hasAttribute($key)) {
                $schoolLocation->$key = $value;
            }
        }
        $schoolId = School::where('external_main_code',$data['brin_nummer'])->value('id');
        if($schoolId){
            $schoolLocation->school_id = $schoolId;
        }
        $schoolLocation->save();
    }

    public function createSchoolLocation($data, $user)
    {
        $data['grading_scale_id'] = 1;
        $data['activated'] = 1;
        if($data = $this->transformDataForSchoolLocation($data)) {
            return $this->createProperty(new SchoolLocation(), $data, $user);
        }
        return false;
    }

    protected function transformDataForSchoolLocation($data)
    {
        $data['education_levels'] = $this->getSchoolLocationLevelsFromData($data);
        if($data['education_levels']) {
            return $data;
        }
        return false;
    }

    protected function getSchoolLocationLevelsFromData($data)
    {
        $niveauString = $data['onderwijsstructuur'];
        if(Str::endsWith($niveauString,';')){
            $niveauString = substr($niveauString,0, -1);
        }
        $separator = ';'; // as per ticket 2144 // substr_count($niveauString,';') ? ';' : '/';

        $niveaus = array_map('strtolower',explode($separator,$niveauString));

        $schoolLocationEducationLevelIds =[];
        $levels = $this->getEducationLevels();
        foreach($niveaus as $niveau){
            if($niveau === 'mavo'){
                $niveau = 'mavo/havo';
            }
            else if($niveau === 'vbo'){
                foreach(['vmbo-tl', 'vmbo-bb', 'vmbo-kb', 'lwoo'] as $niveau){
                    if(isset($levels[$niveau])) {
                        $schoolLocationEducationLevelIds[] = $levels[$niveau];
                    }
                }
                continue;
            }
            if(isset($levels[$niveau])){
                $schoolLocationEducationLevelIds[] = $levels[$niveau];
            } else {
                if($niveau === 'pro') {
                    if(count($niveaus) === 1) {
                        return false; // we doen nu nog even niets met scholen die alleen pro hebben
                    }
                    continue;
                }
                $extra = '';
                if(substr_count($niveau,'/')){
                    $extra = ' Did you use a / instead of an ; to split the levels?';
                }
                throw new SchoolAndSchoolLocationsImportException('Education level not found `'.$niveau.'`.'.$extra.' ('.var_export($data,true).')');
            }
        }
        return $schoolLocationEducationLevelIds;
    }

    protected function getEducationLevels()
    {
        if(!$this->educationLevels){
            $this->educationLevels = array_change_key_case(EducationLevel::pluck('id','name')->toArray(), CASE_LOWER);
        }
        return $this->educationLevels;
    }


    protected function createProperty($class, $data, $user = null)
    {
        $this->inform('going to add '.get_class($class).' => '.$data['name'].' => memory '.$this->getMemorySize());

        $data = $this->enhanceDataWithUser($data, $user);
        $class->fill($data);

        if($class instanceof SchoolLocation){
            if($data['brin_nummer']) {
                $schoolId = School::where('external_main_code', $data['brin_nummer'])->value('id');
                if ($schoolId) {
                    $class->school_id = $schoolId;
                }
            }
        }

        if(!$class->save()){
            throw new SchoolAndSchoolLocationsImportException("Could not add the ".get_class($class)." with name ".$data['name']);
        }

        return $class;
    }

    protected function getMemorySize()
    {
        $size = memory_get_usage(true);
        $unit = array('b', 'kb', 'mb', 'gb', 'tb', 'pb');
        return @round($size / pow(1024, ($i = floor(log($size, 1024)))), 2).' '.$unit[$i];
    }

    protected function enhanceDataWithUser($data, $user = null)
    {
        if (!isset($data['user_id']) || $data['user_id'] === '' || $data['user_id'] === '-') {
            if(!$user){
                throw new SchoolAndSchoolLocationsImportException("No user set to enhance the data");
            }
            $data['user_id'] = $user->getKey();
        }
        return $data;
    }



}
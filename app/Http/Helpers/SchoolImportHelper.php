<?php

namespace tcCore\Http\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use tcCore\EducationLevel;
use tcCore\ExcelSchoolImportManifest;
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

    public function __construct()
    {

    }

    public function setFilePath($path)
    {
        $this->path = storage_path($path);
        return $this;
    }


    public function handleImport()
    {
        DB::beginTransaction();
        try {
            $this->prepareDataFromFile();
            $this->prepareUser();
            $this->handleSchools();
            $this->handleSchoolLocations();
            DB::commit();
        } catch(Throwable $e) {
            DB::rollback();
            dd($e->getMessage());
        }
    }

    protected function prepareUser()
    {
        $this->user = User::where('username','info+ab@test-correct.nl')->orWhere('username','carloschoep+accountmanager@hotmail.com')->orWhere('username','info+testportalaccountmanager@test-correct.nl')->orderBy('created_at','asc')->first();
        if(!$this->user){
            throw new \Exception('Could not find a valid account manager, I`ve searched for info+ab@test-correct.nl, carloschoep+accountmanager@hotmail.com and info+testportalaccountmanager@test-correct.nl');
        }
    }

    protected function handleSchools()
    {
        $data = $this->manifest->getSchools();
        $data->each(function($row){
           if(!School::where('external_main_code',$row['external_main_code'])->exists()){
               $this->createSchool($row,$this->user);
           }
        });
    }

    protected function handleSchoolLocations()
    {
        $data = $this->manifest->getSchoolLocations();
        $data->each(function($row){

        });
    }

    protected function prepareDataFromFile()
    {
        $this->manifest = new ExcelSchoolImportManifest($this->path,false);
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

    public function createSchoolLocation($data, $user)
    {
        $data['school_id'] = optional(School::where('external_main_code',$data['external_main_code'])->first())->getKey();
        $data = $this->transformDataForSchoolLocation($data);
        $schoolLocation = $this->createProperty(new SchoolLocation(),$data,$user);
        Auth::login($user);
        return $this->addSchoolLocationExtras($schoolLocation);
    }

    protected function transformDataForSchoolLocation($data)
    {
        $data['education_levels'] = $this->getSchoolLocationLevelsFromData($data);
        return $data;
    }

    protected function getSchoolLocationLevelsFromData($data)
    {
        $niveauString = $data['onderwijsstructuur'];
        $niveaus = array_map('strtolower',explode("/",$niveauString));
        $schoolLocationEducationLevelIds =[];
        $levels = $this->getEducationLevels();
        foreach($niveaus as $niveau){
            if(isset($levels[$niveau])){
                $schoolLocationEducationLevelIds[] = $levels[$niveau];
            } else {
                throw new \Exception('Education level not found '.$niveau);
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
        $data = $this->enhanceDataWithUser($data, $user);
        $class->fill($data);
        if(!$class->save()){
            throw new \Exception("Could not add the ".get_class($class)." with name ".$data['name']);
        }
        return $class;
    }

    protected function enhanceDataWithUser($data, $user = null)
    {
        if (!isset($data['user_id'])) {
            if(!$user){
                throw new \Exception("No user set to enhance the data");
            }
            $data['user_id'] = $user->getKey();
        }
        return $data;
    }

    protected function addSchoolLocationExtras(SchoolLocation $schoolLocation)
    {
        $year = Date("Y");
        $nextYear = $year + 1;
        if (Date("m") < 8) {
            $nextYear = $year;
            $year--;
        }
        $schoolLocation
            ->addSchoolYearAndPeriod($year, '01-08-' . $year, '31-07-' . $nextYear)
            ->addDefaultSectionsAndSubjects("VO");
        return $schoolLocation;
    }

}
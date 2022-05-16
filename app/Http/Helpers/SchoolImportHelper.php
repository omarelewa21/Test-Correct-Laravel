<?php

namespace tcCore\Http\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use tcCore\EducationLevel;
use tcCore\ExcelSchoolImportManifest;
use tcCore\Jobs\CreateSchoolLocationFromImport;
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

    public function __construct($echoDetails = false)
    {
        set_time_limit(300);
        $this->echoDetails = $echoDetails;
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
            GlobalStateHelper::getInstance()->setQueueAllowed(false);
            GlobalStateHelper::getInstance()->setPreventDemoEnvironmentCreationForSchoolLocation(true);
            $this->inform('Going to start with the file prep');
            $this->prepareDataFromFile();
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
            echo 'done';
        } catch(Throwable $e) {
            DB::rollback();
            logger($e);
            dd($e->getMessage());
        }
    }

    protected function inform($info)
    {
        if($this->echoDetails){
            echo $info.PHP_EOL;
        }
    }

    protected function prepareUser()
    {
        $this->user = User::where('username','info+ab@test-correct.nl')->orWhere('username','carloschoep+accountmanager@hotmail.com')->orWhere('username','info+testportalaccountmanager@test-correct.nl')->orWhere('username','c@teachandlearncompany.com')->orderBy('created_at','asc')->first();
        if(!$this->user){
            throw new \Exception('Could not find a valid account manager, I`ve searched for info+ab@test-correct.nl, carloschoep+accountmanager@hotmail.com, info+testportalaccountmanager@test-correct.nl and c@teachandlearncompany.com');
        }
    }

    protected function handleSchools()
    {
        $data = $this->manifest->getSchools();

        $data->each(function($row){
           if(!School::where('external_main_code',$row['external_main_code'])->exists()){
               if($row['name'] !== '-') {
                   $this->createSchool((array)$row, $this->user);
               }
           }
        });
    }

    protected function  handleSchoolLocations()
    {
        $data = $this->manifest->getSchoolLocations();
        $data->each(function($row){
            if(!SchoolLocation::where('external_main_code',$row['brin_nummer'])->where('external_sub_code',$row['locatie_brin_code_2_karakters_max'])->exists()) {
                if($row['name'] !== '-') {
                    $this->inform('dispatch school location '.$row['name'].' => memory '.$this->getMemorySize());
                    dispatch(new CreateSchoolLocationFromImport($row,$this->user));
//                    $location = $this->createSchoolLocation($row, $this->user);
                }
            }
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
        $data['grading_scale_id'] = 1;
        $data['activated'] = 1;
        if($data = $this->transformDataForSchoolLocation($data)) {
            $schoolLocation = $this->createProperty(new SchoolLocation(), $data, $user);
            return $this->addSchoolLocationExtras($schoolLocation);
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
        $separator = substr_count($niveauString,';') ? ';' : '/';

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
                throw new \Exception('Education level not found '.$niveau.' ('.var_export($data,true).')');
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

        if(!$class->save()){
            throw new \Exception("Could not add the ".get_class($class)." with name ".$data['name']);
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
        $origAuthUser = Auth::user();
        $year = Date("Y");
        $nextYear = $year + 1;
        if (Date("m") < 8) {
            $nextYear = $year;
            $year--;
        }
        $userId = User::where('school_location_id',$schoolLocation->getKey())->value('id');
        if($userId){
            Auth::loginUsingId($userId);
        }
        $schoolLocation
            ->addSchoolYearAndPeriod($year, '01-08-' . $year, '31-07-' . $nextYear)
            ->addDefaultSectionsAndSubjects("VO");
        if($origAuthUser){
            Auth::login($origAuthUser);
        } else {
            Auth::logout();
        }
        return $schoolLocation;
    }

}
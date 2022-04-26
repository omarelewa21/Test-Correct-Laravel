<?php

namespace tcCore\Http\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\UmbrellaOrganization;
use Throwable;

class SchoolImportHelper
{

    protected $umbrellaOrganzationId;
    protected $schoolId;
    protected $schoolLocationId;

    public function handleImport($data)
    {
        DB::beginTransaction();
        try {

            DB::commit();
        } catch(Throwable $e) {
            DB::rollback();
            dd($e->getMessage());
        }
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
        if($this->schoolId){
            $data['school_id'] = $this->schoolId;
        }
        $schoolLocation = $this->createProperty(new SchoolLocation(),$data,$user);
        Auth::login($user);
        return $this->addSchoolLocationExtras($schoolLocation);
    }

    protected function createProperty($class, $data, $user = null)
    {
        $data = $this->enhanceDataWithUser($data, $user);
        $class->fill($data);
        if(!$class->save()){
            throw new \Exception("Could not add the ".get_class($class)." with name ".$data['name']);
        }
        $propertyIdName = sprintf("%sId",Str::lcfirst(get_class($class)));
        $this->$propertyIdName = $class->getKey();
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
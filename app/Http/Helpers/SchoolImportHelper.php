<?php

namespace tcCore\Http\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use tcCore\EducationLevel;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\UmbrellaOrganization;
use Throwable;

class SchoolImportHelper
{

    protected $umbrellaOrganzationId;
    protected $schoolId;
    protected $schoolLocationId;
    protected $schoolLocationTransformer = [
        'BRIN NUMMER' => 'external_main_code',
        'Locatie brin code (2 karakters max)' => 'external_sub_code',
        'naam' => 'name',
        'Klantcode' => 'customer_code',
        'Vestiging - adres' => 'main_address',
        'Vestiging - postcode' => 'main_postal',
        'Vestiging - stad' => 'main_city',
        'Vestiging - Land' => 'main_country',
        'TELEFOONNUMMER' => 'main_phonenumber',
        'INTERNETADRES' => 'internetaddress',
        'ONDERWIJSSTRUCTUUR' => 'ONDERWIJSSTRUCTUUR',
        'Factuuradres - adres' => 'invoice_address',
        'Factuuradres - Postcode' => 'invoice_postal',
        'Factuuradres - Stad' => 'invoice_city',
        'Factuuradres - Land' => 'invoice_country',
        'Bezoekadres - adres' => 'visit_address',
        'Bezoekadres - Postcode' => 'visit_postal',
        'Bezoekadres - Stad' => 'visit_city',
        'Bezoekadres - Land' => 'visit_country',
        'Hubspot ID' => 'company_id',
    ];

    protected $schoolTransformer = [
        'Naam scholengemeenschap' => 'name',
        'BRIN4' => 'external_main_code',
        'Klantcode' => 'customer_code',
        'Vestigingsadres - adres' => 'main_address',
        'Vestigingsadres - postcode' => 'main_postal',
        'Vestigingsadres - stad' => 'main_city',
        'Vestigingsadres - land' => 'main_country',
        'factuuradres - adres' => 'invoice_address',
        'factuuradres - postcode' => 'invoice_postal',
        'factuuradres -stad' => 'invoice_city',
        'factuuradres - land' => 'invoice_country',
    ];
    protected $educationLevels;

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
        $data = $this->transformForUmbrellaOrganization($data);
        return $this->createProperty(new UmbrellaOrganization(),$data,$user);
    }

    public function createSchool($data, $user = null)
    {
        $this->schoolLocationId = null;
        if($this->umbrellaOrganzationId){
            $data['umbrella_organization_id'] = $this->umbrellaOrganzationId;
        }
        $data = $this->transformDataForSchool($data);
        return $this->createProperty(new School(),$data,$user);
    }

    public function createSchoolLocation($data, $user)
    {
        if($this->schoolId){
            $data['school_id'] = $this->schoolId;
        }
        $data = $this->transformDataForSchoolLocation($data);
        $schoolLocation = $this->createProperty(new SchoolLocation(),$data,$user);
        Auth::login($user);
        return $this->addSchoolLocationExtras($schoolLocation);
    }

    protected function transformDataForSchoolLocation($data)
    {
        $data = $this->transformDataForProperty($this->schoolLocationTransformer,$data);
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

    protected function transformDataForSchool($data)
    {
        return $this->transformDataForProperty($this->schoolTransformer,$data);
    }

    protected function transformDataForProperty($transformer,$data)
    {
        $transformer = array_change_key_case($transformer, CASE_LOWER);
        $data = array_change_key_case($data, CASE_LOWER);

        foreach($transformer as $from => $to){
            if(isset($data[$from]) && $data[$from]){
                $data[$to] = $data[$from];
                unset($data[$from]);
            } else {
                $data[$to] = '-';
            }
        }
        return $data;
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
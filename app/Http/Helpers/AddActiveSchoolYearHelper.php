<?php

namespace tcCore\Http\Helpers;



use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use tcCore\Lib\Repositories\PeriodRepository;
use tcCore\SchoolLocation;

class AddActiveSchoolYearHelper
{

    private $schoolLocations;

    public function __construct(){
        $this->schoolLocations = SchoolLocation::onlyVo()->get();
    }

    public function getSchoolLocations()
    {
        return $this->schoolLocations;
    }

    public function getWithoutCurrentSchoolYear() {

        return $this->schoolLocations->filter(function($schoolLocation){
            try {
                PeriodRepository::getCurrentPeriodForSchoolLocation($schoolLocation) == null;

            } catch(\Exception $e) {
                if (Str::of($e->getMessage())->contains('No valid period found for school location')) {
                    return true;
                }
            }
            return false;
        });
    }


}

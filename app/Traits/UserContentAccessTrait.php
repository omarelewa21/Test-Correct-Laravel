<?php
namespace tcCore\Traits;

use Illuminate\Support\Facades\DB;

trait UserContentAccessTrait{

    private function switchScopeFilteredSubQueryForDifferentScenarios($user,$demoSubject)
    {
        if ($user->hasSingleSchoolLocation()) {
            return $this->getSubQueryForScopeFilteredSingleSchoolLocation($user,$demoSubject);
        }
        return $this->getSubQueryForScopeFilteredMultipleSchoolLocations($user,$demoSubject);
    }

    private function getSubQueryForScopeFilteredSingleSchoolLocation($user,$demoSubject)
    {
        return DB::raw('(' . $this->getQueryGetItemsFromSchoolLocationAuthoredByUser($user) .
            ' union ' .
            $this->getQueryGetItemsFromSectionWithinSchoolLocation($user,$demoSubject) .
            ') as t1'
        );
    }

    private function getSubQueryForScopeFilteredMultipleSchoolLocations($user,$demoSubject)
    {
        return DB::raw('(' . $this->getQueryGetItemsFromSchoolLocationAuthoredByUser($user) .
            ' union ' .
            $this->getQueryGetItemsFromAllSchoolLocationsAuthoredByUserCurrentlyTaughtByUserInActiveSchoolLocation($user,$demoSubject) .
            ' union ' .
            $this->getQueryGetItemsFromSectionWithinSchoolLocation($user,$demoSubject) .
            ' ) as t1'
        );
    }
}
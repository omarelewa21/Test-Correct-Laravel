<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 09/04/2020
 * Time: 10:59
 */

namespace tcCore\Http\Helpers;


use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\UmbrellaOrganization;
use tcCore\User;

class SchoolHelper
{

    public static function getTijdelijkeDocentenaccountsSchoolLocation()
    {
        return SchoolLocation::where('customer_code','TC-tijdelijke-docentaccounts')->first();
    }

    public function getRelatedSchoolLocationIds($user){
        if($user->hasRole('Account manager')) {
            return SchoolLocation::where(function ($query) use ($user) {
                $query->whereIn('school_id', $this->getRelatedSchoolIds($user))
                    ->orWhere('user_id', $user->getKey());
            })->pluck('id')->all();
        }
        else{
            if($user->school != null){
                return [$user->school->getKey()];
            }
            else if($user->schoolLocation != null && $user->schoolLocation->school != null){
                return [$user->schoolLocation->school->getKey()];
            }
            else{
                return [];
            }
        }
    }

    public function getRelatedSchoolIds($user)
    {
        if($user->hasRole('Account manager')) {
            return School::where(function ($query) use ($user) {
                $query->whereIn('umbrella_organization_id', function ($query) use ($user) {
                    $query->select('id')
                        ->from(with(new UmbrellaOrganization())->getTable())
                        ->where('user_id', $user->getKey())
                        ->whereNull('deleted_at');
                })->orWhere('user_id', $user->getKey());
            })->pluck('id')->all();
        }
        else{
            return [$user->school_location_id];
        }
    }
}
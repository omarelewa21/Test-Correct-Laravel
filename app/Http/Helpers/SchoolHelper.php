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
use tcCore\Teacher;
use tcCore\UmbrellaOrganization;
use tcCore\User;

class SchoolHelper
{

    public static function getSomeTeacherBySchoolLocationId($schoolLocationId)
    {
        return User::query()->select('users.*')->join('user_roles', function($join){
            $join->on('users.id', '=', 'user_roles.user_id');
            $join->where('user_roles.role_id', '=', 1);
            $join->whereNull('user_roles.deleted_at');
        })->where('school_location_id', $schoolLocationId)->first();
    }

    public static function getBaseDemoSchoolUser()
    {
        // we do want a teacher so we've got to make sure we've got a teacher here
        return User::query()->select('users.*')->join('user_roles', function($join) {
            $join->on('users.id', '=', 'user_roles.user_id');
            $join->where('user_roles.role_id', '=', 1);
            $join->whereNull('user_roles.deleted_at');
        })->where('school_location_id', self::getTempTeachersSchoolLocation()->getKey())->first();

    }

    public static function getTempTeachersSchoolLocation()
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
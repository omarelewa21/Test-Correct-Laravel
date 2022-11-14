<?php
/**
 * Created by PhpStorm.
 * User: erik
 * Date: 09/04/2020
 * Time: 10:59
 */

namespace tcCore\Http\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Response;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\Teacher;
use tcCore\UmbrellaOrganization;
use tcCore\User;

class SchoolHelper
{

    public static function getSomeTeacherOrSchoolManagerBySchoolLocationId($schoolLocationId)
    {
        return User::query()->select('users.*')->join('user_roles', function($join){
            $join->on('users.id', '=', 'user_roles.user_id');
            $join->whereIn('user_roles.role_id', [1,6]);
            $join->whereNull('user_roles.deleted_at');
        })->where('school_location_id', $schoolLocationId)->orderBy('created_at','asc')->orderBy('id','asc')->first();
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
        return SchoolLocation::where('customer_code','OUD TC-tijdelijke-docentaccounts')
            ->orWhere('customer_code','TC-tijdelijke-docentaccounts')
            ->orderBy('id')
            ->first();
    }

    public static function isTempTeachersSchoolLocation(SchoolLocation $schoolLocation)
    {
        return in_array($schoolLocation->customer_code, [
            'OUD TC-tijdelijke-docentaccounts',
            'TC-tijdelijke-docentaccounts',
        ]);
    }

    public static function denyIfTempTeacher() {
        if (Auth::user()->is_temp_teacher) {
            return Response::make('Request denied because teacher is in temp school location', 500);
		}
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
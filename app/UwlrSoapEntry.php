<?php

namespace tcCore;

use Illuminate\Database\Eloquent\Model;

class UwlrSoapEntry extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['uwlr_soap_result_id', 'key', 'object'];

    public static function deleteImportDataForSchoolLocationId($id, $resultSetId = false)
    {
        SchoolClass::whereSchoolLocationId($id)->each(function ($schoolClass) {
            $schoolClass->teacher()->forceDelete();
            $schoolClass->students()->forceDelete();
            $schoolClass->forceDelete();
        });

        User::whereSchoolLocationId($id)->each(function($user) {
            $user->eckidFromRelation()->forceDelete();
            if(!$user->isA('school manager')) {
                $user->forceDelete();
            }
        });
        $schoolLocation = SchoolLocation::find($id);
        if($resultSetId) {
            $set = UwlrSoapResult::find($resultSetId);
            $set->entries()->forceDelete();
            $set->forceDelete();
        } else {
            UwlrSoapResult::where('brin_code', $schoolLocation->external_main_code)->where('dependance_code', $schoolLocation->external_sub_code)->get()->each(function ($result) {
                $result->entries()->forceDelete();
                $result->forceDelete();
            });
        }
    }

    public static function deleteImportData()
    {
        SchoolLocation::where('name','Magister Schoollocatie')->orWhere('name','somtoday Schoollocatie')->get()->each(function(SchoolLocation $schoolLocation){
            UwlrSoapEntry::deleteImportDataForSchoolLocationId($schoolLocation->getKey());
//            SchoolClass::whereSchoolLocationId($schoolLocation->getKey())->each(function ($schoolClass) {
//                $schoolClass->teacher()->forceDelete();
//                $schoolClass->students()->forceDelete();
//                $schoolClass->forceDelete();
//            });
//
//            User::whereSchoolLocationId($schoolLocation->getKey())->each(function($user) {
//                $user->eckidFromRelation()->forceDelete();
//                if(!$user->isA('school manager')) {
//                    $user->forceDelete();
//                }
//            });
        });

//        UwlrSoapResult::all()->each->forceDelete();
//        UwlrSoapEntry::all()->each->forceDelete();
    }
}

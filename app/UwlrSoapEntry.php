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

    public static function deleteImportData()
    {
        SchoolLocation::where('name','Magister Testservice')->orWhere('name','somtoday testservice')->get()->each(function(SchoolLocation $schoolLocation){
            SchoolClass::whereSchoolLocationId($schoolLocation->getKey())->each(function ($schoolClass) {
                $schoolClass->teacher()->forceDelete();
                $schoolClass->students()->forceDelete();
                $schoolClass->forceDelete();
            });

            User::whereSchoolLocationId($schoolLocation->getKey())->each(function($user) {
                $user->eckidFromRelation()->forceDelete();
                $user->forceDelete();
            });
        });

        UwlrSoapResult::all()->each->forceDelete();
        UwlrSoapEntry::all()->each->forceDelete();
    }
}

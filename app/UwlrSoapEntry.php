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

    const DATASOURCES = [
        [
            'name'            => 'Magister TestService',
            'client_code'     => 'OV',
            'client_name'     => 'overig',
            'school_year'     => '2019-2020',
            'brin_code'       => '99DE',
            'dependance_code' => '00',
        ], [
            'name'            => 'SomeToday TestService',
            'client_code'     => 'OV',
            'client_name'     => 'overige',
            'school_year'     => '2019-2020',
            'brin_code'       => '06SS',
            'dependance_code' => '00',
        ],
    ];

    public static function deleteMagisterData()
    {
        $data = collect(UwlrSoapEntry::DATASOURCES)->first(function ($data) {
            return $data['name'] == 'Magister TestService';
        });

        $schoolLocation = SchoolLocation::firstWhere('external_main_code', $data['brin_code']);

        SchoolClass::whereSchoolLocationId($schoolLocation->getKey())->each(function ($schoolClass) {
            $schoolClass->teacher()->forceDelete();
            $schoolClass->students()->forceDelete();
            $schoolClass->forceDelete();
        });

        User::whereSchoolLocationId($schoolLocation->getKey())->each(function($user) {
            $user->eckidFromRelation()->forceDelete();
            $user->forceDelete();
        });
        UwlrSoapResult::all()->each->forceDelete();
        UwlrSoapEntry::all()->each->forceDelete();
    }
}

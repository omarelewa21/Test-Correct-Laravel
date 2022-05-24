<?php

namespace tcCore\Http\Controllers;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use tcCore\SamlMessage;
use tcCore\School;
use tcCore\SchoolLocation;
use tcCore\User;

class EntreeTestSession extends Controller {
    public function __invoke()
    {

        $data = (object)[
            'emailAddress' => Str::random(6).'@sobit.nl',
            'role' => 'teacher',
            'encryptedEckId' => Crypt::encryptString('xxxx123'),
            'brin' => '00BD02',
            'location' => SchoolLocation::where('customer_code','COEN23456')->first(),
            'brin4ErrorDetected' => false,
            'lastName' => 'Dohmen',
            'nameSuffix' => null,
            'firstName' => 'Erikr',
        ];

        if(request()->has('schoolId')){
            $data->location = null;
            $data->school = School::find(request()->get('schoolId'));
        }

        $data->schoolId = (property_exists($data,'school') && $data->school) ? $data->school->getKey() : null;
        $data->locationId = property_exists($data,'location') && $data->location ? $data->location->getKey() : null;
        $data->school = null;
        $data->location = null;
        $data->userId = request()->get('userId',null);
        $data->user = null;

        $samlId = SamlMessage::create([
            'data' => $data,
            'eck_id' => 'not needed',
            'message_id' => 'not needed',
        ]);
        return Response::redirectTo(route('onboarding.welcome.entree',['samlId' => $samlId->uuid]));
    }
}
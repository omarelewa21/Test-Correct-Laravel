<?php

namespace tcCore\Http\Controllers;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use tcCore\SchoolLocation;
use tcCore\User;

class EntreeTestSession extends Controller {
    public function __invoke()
    {
        $data = (object)[
            'emailAddress' => Str::random(6).'@sobit.nl',
            'role' => 'teacher',
            'encryptedEckId' => Crypt::encryptString('xxxx12'),
            'brin' => 'K99900',
            'location' => SchoolLocation::where('customer_code','k999')->first(),
            'brin4ErrorDetected' => false,
            'lastName' => 'Dohmen',
        ];
        if(request()->has('withT')){
            if(request()->has('userId')){
                $data->user = User::find(request()->get('userId'));
            }
        }
        session(['entreeData' => $data]);
        return Response::redirectTo(route('onboarding.welcome.entree'));
    }
}
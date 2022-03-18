<?php

namespace tcCore\Http\Controllers;

use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Response;
use tcCore\SchoolLocation;
use tcCore\User;

class EntreeTestSession extends Controller {
    public function __invoke()
    {
        $data = (object)[
            'email' => 'erik@sobit.nl',
            'role' => 'teacher',
            'encryptedEckId' => Crypt::encryptString('xxxx12'),
            'brin' => 'K99900',
            'location' => SchoolLocation::where('customer_code','k999')->first(),
            'brin4ErrorDetected' => false,
        ];
        if(request()->has('withT')){
            $data->email = 'erik@sobit.nl';
            if(request()->has('userId')){
                $data->user = User::find(request()->get('userId'));
            }
        }
        session(['entreeData' => $data]);
        return Response::redirectTo(route('onboarding.welcome.entree'));
    }
}
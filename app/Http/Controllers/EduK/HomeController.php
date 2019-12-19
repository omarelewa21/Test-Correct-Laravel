<?php

namespace tcCore\Http\Controllers\EduK;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rules\In;
use SoapHeader;
use stdClass;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Helpers\EduIxService;
use tcCore\Http\Requests\CreateUserEduIxRequest;
use tcCore\Http\Requests\CreateUserRequest;
use tcCore\Lib\User\Factory;
use tcCore\SchoolLocation;
use tcCore\User;
use tcCore\UserRole;

class HomeController extends Controller
{
    public function create($ean, $sessionId, $signature)
    {
        $service = new EduIxService($sessionId, $signature);

        return [
            'eduProfile' => $service->getEduProfile(),
            'personCredit' => $service->getPersonCredit(),
            'schoolCredit' => $service->getSchoolCredit(),
        ];
    }

    /**
     * Store a newly created user in storage.
     *
     * @param CreateUserRequest $request
     * @return Response
     */
    public function store(CreateUserEduIxRequest $request)
    {
        $service = new EduIxService(
            request('session_id'),
            request('edu_ix_signature')
        );


        $userFactory = new Factory(new User());
        $user = $userFactory->generate(array_merge(
            $request->all(), [
                'school_location_id' =>  SchoolLocation::where('edu_ix_organisation_id', $service->getHomeOrganizationId())->first()->getKey(),
                'user_roles' => 3,
            ])
        );

        if ($user !== false) {
            return Response::make($user, 200);
        } else {
            return Response::make('Failed to create user', 500);
        }
    }


    public function index()
    {
//        dd(Input::all());
        (new EduIxService(Input::get('redirectSessionID'), Input::get('signature')))->script();
    }
}


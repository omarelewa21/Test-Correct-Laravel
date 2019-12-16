<?php

namespace tcCore\Http\Controllers\EduK;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Validation\Rules\In;
use SoapHeader;
use stdClass;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Helpers\EduIxService;
use tcCore\Http\Requests\CreateUserEduIxRequest;
use tcCore\Http\Requests\CreateUserRequest;
use tcCore\Lib\User\Factory;
use tcCore\User;

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
        $userFactory = new Factory(new User());

        $user = $userFactory->generate($request->all());
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


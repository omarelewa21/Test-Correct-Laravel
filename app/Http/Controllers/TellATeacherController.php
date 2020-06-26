<?php

namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use tcCore\Http\Requests\CreateUserRequest;

class TellATeacherController extends Controller
{
    public function store(CreateUserRequest $request)
    {
        // signal the DemoTeacherRegistration model to record the user in a DemoTeacherRegistration object;
        $request->merge([
            'shouldRegisterUser' => true,
        ]);

        return (new UsersController)->store($request);
    }
}

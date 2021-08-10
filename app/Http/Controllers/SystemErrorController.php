<?php

namespace tcCore\Http\Controllers;

use Bugsnag\BugsnagLaravel\Facades\Bugsnag;
use tcCore\Http\Requests\SystemErrorRequest;

class SystemErrorController extends Controller
{
    public function addErrorToBugsnag(SystemErrorRequest $request)
    {
        Bugsnag::notifyException(new \Exception($request->message));
        exit();
    }
}
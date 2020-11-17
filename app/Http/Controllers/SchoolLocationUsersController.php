<?php namespace tcCore\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use tcCore\Http\Helpers\DemoHelper;
use tcCore\Http\Requests;
use tcCore\Http\Requests\DuplicateTestRequest;
use tcCore\SchoolLocation;
use tcCore\Test;
use tcCore\Http\Controllers\Controller;
use tcCore\Http\Requests\CreateTestRequest;
use tcCore\Http\Requests\UpdateTestRequest;

class SchoolLocationUsersController extends Controller {

    public function index()
    {
        return Auth::user()->allowedSchoolLocations;
    }

    public function update(Request $request)
    {
        $schoolLocation = SchoolLocation::whereUuid($request->school_location)->first();
        if (! Auth::user()->isAllowedToSwitchToSchoolLocation($schoolLocation)) {
            abort(403);
        }

        $user = Auth::user()->schoolLocation()->associate($schoolLocation);
        $user->save();

        return $user;
    }
}

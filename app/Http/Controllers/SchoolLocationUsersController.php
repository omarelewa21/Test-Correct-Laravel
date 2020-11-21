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
use tcCore\User;

class SchoolLocationUsersController extends Controller {

    public function index()
    {
        return Auth::user()->allowedSchoolLocations->map(function($location) {
            return (object) [
                'id' => $location->id,
                'uuid' => $location->uuid,
                'name' => $location->name,
                'active' => $location->is(Auth::user()->schoolLocation),
            ];
        });
    }

    public function update(Request $request)
    {
        $schoolLocation = SchoolLocation::whereUuid($request->school_location)->first();
        if (! Auth::user()->isAllowedToSwitchToSchoolLocation($schoolLocation)) {
            abort(403);
        }

        $user = Auth::user()->schoolLocation()->associate($schoolLocation);
        $user->save();

        return $user->refresh()->allowedSchoolLocations->map(function($location) {
            return (object) [
                'id' => $location->id,
                'uuid' => $location->uuid,
                'name' => $location->name,
                'active' => $location->is(Auth::user()->schoolLocation),
            ];
        });
    }

    public function store(Request $request)
    {
        $user = User::whereUuid($request->get('user_uuid'))->first();
        $schoolLocation = SchoolLocation::whereUuid($request->get('school_location'))->first();

        $user->addSchoolLocation($schoolLocation);
    }

    public function delete(Request $request) {
        $user = User::whereUuid($request->get('user_uuid'))->first();
        $schoolLocation = SchoolLocation::whereUuid($request->get('school_location'))->first();

        $user->removeSchoolLocation($schoolLocation);
    }

    public function getExistingTeachers(){


        if (!Auth::user()->hasRole('Administrator')) {
            abort(403);
        }

        if (null === Auth::user()->school_id) {
            abort(404);
        }

        /**
         * select *
         * from users
         * inner join user_roles on (users.id = user_roles.user_id and user_roles.`role_id` = 1)
         * where school_id = 2
         */

        return User::join('user_roles', function ($join) {
            $join->on('users.id', '=', 'user_roles.user_id')
                ->where('user_roles.role_id', '=', 1); // teacher
        })->where('school_id', Auth::user()->school_id)->get();
    }
}
